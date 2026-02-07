<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Types;

use CuyZ\Valinor\Compiler\Compiler;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Tests\Unit\UnitTestCase;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\ShapedArrayElementDuplicatedKey;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\ArrayKeyType;
use CuyZ\Valinor\Type\Types\ArrayType;
use CuyZ\Valinor\Type\Types\GenericType;
use CuyZ\Valinor\Type\Types\IntegerValueType;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\NativeFloatType;
use CuyZ\Valinor\Type\Types\NativeIntegerType;
use CuyZ\Valinor\Type\Types\NativeStringType;
use CuyZ\Valinor\Type\Types\NonEmptyStringType;
use CuyZ\Valinor\Type\Types\ShapedArrayElement;
use CuyZ\Valinor\Type\Types\ShapedArrayType;
use CuyZ\Valinor\Type\Types\StringValueType;
use CuyZ\Valinor\Type\Types\UnionType;
use PHPUnit\Framework\Attributes\TestWith;
use stdClass;

final class ShapedArrayTypeTest extends UnitTestCase
{
    /** @var ShapedArrayElement[] */
    private array $elements;

    private ArrayType $unsealedType;

    private ShapedArrayType $type;

    protected function setUp(): void
    {
        parent::setUp();

        $this->elements = [
            'foo' => new ShapedArrayElement(new StringValueType('foo'), new NativeStringType()),
            1337 => new ShapedArrayElement(new IntegerValueType(1337), new NativeIntegerType(), true),
        ];
        $this->unsealedType = new ArrayType(
            new ArrayKeyType([StringValueType::from("'unsealed-key'")]),
            new NativeFloatType(),
        );

        $this->type = new ShapedArrayType(elements: $this->elements, isUnsealed: true, unsealedType: $this->unsealedType);
    }

    public function test_shape_properties_can_be_retrieved(): void
    {
        self::assertSame($this->unsealedType, $this->type->unsealedType());
        self::assertSame($this->elements, $this->type->elements);
    }

    public function test_duplicate_element_key_throws_exception(): void
    {
        $this->expectException(ShapedArrayElementDuplicatedKey::class);
        $this->expectExceptionMessage('Key `42` cannot be used several times in shaped array.');

        ShapedArrayType::from(
            elements: [
                new ShapedArrayElement(new IntegerValueType(42), new NativeStringType()),
                new ShapedArrayElement(new IntegerValueType(42), new NativeStringType()),
            ],
            isUnsealed: false,
        );
    }

    public function test_string_value_is_correct(): void
    {
        self::assertSame("array{foo: string, 1337?: int, ...array<'unsealed-key', float>}", $this->type->toString());
    }

    // Without additional values
    #[TestWith([['foo' => 'foo', 1337 => 42]])]
    #[TestWith([['foo' => 'foo']])]
    // With additional values
    #[TestWith([['foo' => 'foo', 1337 => 42, 'unsealed-key' => 42.1337]])]
    #[TestWith([['foo' => 'foo', 'unsealed-key' => 42.1337]])]
    public function test_accepts_correct_values(mixed $value): void
    {
        self::assertTrue($this->type->accepts($value));
        self::assertTrue($this->compiledAccept($this->type, $value));
    }

    // Without additional values
    #[TestWith([['foo' => 42]])]
    #[TestWith([['foo' => 'foo', 1337 => 'foo']])]
    #[TestWith([['foo' => new stdClass()]])]
    #[TestWith([['bar' => 'foo']])]
    // With invalid additional values
    #[TestWith([['foo' => 'foo', 42 => '']])]
    // Others
    #[TestWith([null])]
    #[TestWith(['Schwifty!'])]
    #[TestWith([42.1337])]
    #[TestWith([404])]
    #[TestWith([false])]
    #[TestWith([new stdClass()])]
    public function test_does_not_accept_incorrect_values(mixed $value): void
    {
        self::assertFalse($this->type->accepts($value));
        self::assertFalse($this->compiledAccept($this->type, $value));
    }

    public function test_does_not_accept_value_when_unsealed_type_is_vacant(): void
    {
        $type = ShapedArrayType::from(
            elements: [new ShapedArrayElement(new StringValueType('foo'), new NativeStringType())],
            isUnsealed: true,
            unsealedType: new GenericType('T', new NativeStringType()),
        );

        self::assertFalse($type->accepts(['foo' => 'foo']));
        self::assertFalse($this->compiledAccept($type, ['foo' => 'foo']));
    }

    public function test_matches_valid_array_shaped_type(): void
    {
        $otherA = ShapedArrayType::from(
            elements: [
                new ShapedArrayElement(new StringValueType('foo'), new NativeStringType()),
                new ShapedArrayElement(new IntegerValueType(1337), new NativeIntegerType()),
            ],
            isUnsealed: true,
            unsealedType: new ArrayType(ArrayKeyType::string(), new NativeFloatType()),
        );

        $otherB = ShapedArrayType::from(
            elements: [
                new ShapedArrayElement(new StringValueType('foo'), new NativeStringType()),
            ],
            isUnsealed: true,
            unsealedType: new ArrayType(ArrayKeyType::string(), new NativeFloatType()),
        );

        $otherC = ShapedArrayType::from(
            [new ShapedArrayElement(new StringValueType('foo'), new NativeStringType())],
            isUnsealed: true,
        );

        self::assertTrue($this->type->matches($otherA));
        self::assertTrue($this->type->matches($otherB));
        self::assertTrue($this->type->matches($otherC));
    }

    public function test_unsealed_shaped_array_matches_non_unsealed_shaped_array(): void
    {
        $unsealedShapedArray = ShapedArrayType::from(
            elements: [
                new ShapedArrayElement(new IntegerValueType(42), new NativeStringType()),
            ],
            isUnsealed: true,
        );

        $shapedArray = ShapedArrayType::from(
            elements: [
                new ShapedArrayElement(new IntegerValueType(42), new NativeStringType()),
            ],
            isUnsealed: false,
        );

        self::assertTrue($unsealedShapedArray->matches($shapedArray));
    }

    public function test_does_not_match_invalid_array_shaped_type_element(): void
    {
        $type = new ShapedArrayType(
            elements: [
                'foo' => new ShapedArrayElement(new StringValueType('foo'), new NativeStringType()),
                'bar' => new ShapedArrayElement(new StringValueType('bar'), new NativeStringType()),
                1337 => new ShapedArrayElement(new IntegerValueType(1337), new NativeIntegerType(), true),
            ],
            isUnsealed: true,
            unsealedType: new ArrayType(
                new ArrayKeyType([StringValueType::from("'unsealed-key'")]),
                new NativeFloatType(),
            ),
        );

        // Valid unsealed type, invalid shaped array element type
        $otherA = ShapedArrayType::from(
            elements: [
                new ShapedArrayElement(new StringValueType('foo'), new NativeStringType()), // valid
                new ShapedArrayElement(new IntegerValueType(1337), new NativeFloatType()), // invalid
            ],
            isUnsealed: true,
            unsealedType: new ArrayType(ArrayKeyType::string(), new NativeFloatType()),
        );

        // Valid unsealed type, invalid shaped array element key
        $otherB = ShapedArrayType::from(
            elements: [new ShapedArrayElement(new StringValueType('bar'), new NativeStringType())],
            isUnsealed: true,
            unsealedType: new ArrayType(ArrayKeyType::string(), new NativeFloatType()),
        );

        // Valid unsealed type, missing required element
        $otherC = ShapedArrayType::from(
            elements: [],
            isUnsealed: true,
            unsealedType: new ArrayType(ArrayKeyType::string(), new NativeFloatType()),
        );

        // Invalid unsealed type, valid shaped array element
        $otherD = ShapedArrayType::from(
            elements: [new ShapedArrayElement(new StringValueType('foo'), new NativeStringType())],
            isUnsealed: true,
            unsealedType: new ArrayType(ArrayKeyType::string(), new NativeStringType()),
        );

        // Invalid unsealed type key, valid shaped array element
        $otherE = ShapedArrayType::from(
            elements: [new ShapedArrayElement(new StringValueType('foo'), new NativeStringType())],
            isUnsealed: true,
            unsealedType: new ArrayType(ArrayKeyType::integer(), new NativeFloatType()),
        );

        self::assertFalse($type->matches($otherA));
        self::assertFalse($type->matches($otherB));
        self::assertFalse($type->matches($otherC));
        self::assertFalse($type->matches($otherD));
        self::assertFalse($type->matches($otherE));
    }

    public function test_matches_valid_generic_array_type(): void
    {
        $other = new ArrayType(
            ArrayKeyType::default(),
            new UnionType(new NativeStringType(), new NativeIntegerType(), new NativeFloatType()),
        );

        self::assertTrue($this->type->matches($other));
    }

    public function test_does_not_match_invalid_generic_array_type(): void
    {
        $otherA = new ArrayType(ArrayKeyType::string(), new UnionType(new NativeStringType(), new NativeIntegerType()));
        $otherB = new ArrayType(ArrayKeyType::integer(), new UnionType(new NativeStringType(), new NativeIntegerType()));
        $otherC = new ArrayType(ArrayKeyType::default(), new UnionType(new NativeStringType(), new NativeIntegerType()));
        $otherD = new ArrayType(ArrayKeyType::default(), new NativeIntegerType());

        self::assertFalse($this->type->matches($otherA));
        self::assertFalse($this->type->matches($otherB));
        self::assertFalse($this->type->matches($otherC));
        self::assertFalse($this->type->matches($otherD));
    }

    public function test_does_not_match_other_type(): void
    {
        self::assertFalse($this->type->matches(new FakeType()));
    }

    public function test_matches_mixed_type(): void
    {
        self::assertTrue($this->type->matches(new MixedType()));
    }

    public function test_matches_union_containing_valid_type(): void
    {
        $unionType = new UnionType(
            new FakeType(),
            clone $this->type,
            new FakeType(),
        );

        self::assertTrue($this->type->matches($unionType));
    }

    public function test_does_not_match_union_containing_invalid_element(): void
    {
        $unionType = new UnionType(
            new FakeType(),
            ShapedArrayType::from(
                elements: [new ShapedArrayElement(new StringValueType('bar'), new NativeStringType())],
                isUnsealed: true,
                unsealedType: new ArrayType(ArrayKeyType::integer(), new NonEmptyStringType()),
            ),
            new FakeType(),
        );

        self::assertFalse($this->type->matches($unionType));
    }

    public function test_does_not_match_union_containing_invalid_unsealed_type(): void
    {
        $unionType = new UnionType(
            new FakeType(),
            ShapedArrayType::from(
                elements: [new ShapedArrayElement(new StringValueType('foo'), new NativeStringType())],
                isUnsealed: true,
                unsealedType: new ArrayType(ArrayKeyType::string(), new NativeStringType()),
            ),
            new FakeType(),
        );

        self::assertFalse($this->type->matches($unionType));
    }

    public function test_traverse_type_yields_sub_types(): void
    {
        $unsealedType = new ArrayType(ArrayKeyType::integer(), new FakeType());
        $subTypeA = new FakeType();
        $subTypeB = new FakeType();

        $type = ShapedArrayType::from(
            elements: [
                new ShapedArrayElement(new StringValueType('foo'), $subTypeA),
                new ShapedArrayElement(new StringValueType('bar'), $subTypeB),
            ],
            isUnsealed: true,
            unsealedType: $unsealedType,
        );

        self::assertSame([$subTypeA, $subTypeB, $unsealedType], $type->traverse());
    }

    public function test_native_type_is_correct(): void
    {
        self::assertSame(
            'array',
            (new ShapedArrayType([
                new ShapedArrayElement(new IntegerValueType(42), new NativeIntegerType()),
                new ShapedArrayElement(new StringValueType('foo'), new NativeStringType()),
            ]))->nativeType()->toString(),
        );

        self::assertSame(
            'array',
            ShapedArrayType::from(
                elements: [
                    new ShapedArrayElement(new IntegerValueType(42), new NativeIntegerType()),
                    new ShapedArrayElement(new StringValueType('foo'), new NativeStringType()),
                ],
                isUnsealed: true,
                unsealedType: ArrayType::native(),
            )->nativeType()->toString(),
        );
    }

    private function compiledAccept(Type $type, mixed $value): bool
    {
        /** @var bool */
        return eval('return ' . $type->compiledAccept(Node::variable('value'))->compile(new Compiler())->code() . ';');
    }
}
