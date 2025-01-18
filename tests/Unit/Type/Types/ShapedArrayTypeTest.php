<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Types;

use CuyZ\Valinor\Compiler\Compiler;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Tests\Fake\Type\FakeCompositeType;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\ShapedArrayElementDuplicatedKey;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\ArrayKeyType;
use CuyZ\Valinor\Type\Types\ArrayType;
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
use PHPUnit\Framework\TestCase;
use stdClass;

final class ShapedArrayTypeTest extends TestCase
{
    /** @var ShapedArrayElement[] */
    private array $elements;

    private ArrayType $unsealedType;

    private ShapedArrayType $type;

    protected function setUp(): void
    {
        parent::setUp();

        $this->elements = [
            new ShapedArrayElement(new StringValueType('foo'), new NativeStringType()),
            new ShapedArrayElement(new IntegerValueType(1337), new NativeIntegerType(), true),
        ];
        $this->unsealedType = new ArrayType(
            ArrayKeyType::from(StringValueType::from("'unsealed-key'")),
            new NativeFloatType(),
        );

        $this->type = ShapedArrayType::unsealed($this->unsealedType, ...$this->elements);
    }

    public function test_shape_properties_can_be_retrieved(): void
    {
        self::assertSame($this->unsealedType, $this->type->unsealedType());
        self::assertSame($this->elements, $this->type->elements());
    }

    public function test_duplicate_element_key_throws_exception(): void
    {
        $this->expectException(ShapedArrayElementDuplicatedKey::class);
        $this->expectExceptionCode(1631283279);
        $this->expectExceptionMessage('Key `42` cannot be used several times in shaped array signature `array{42: string, 42: string}`.');

        new ShapedArrayType(
            new ShapedArrayElement(new IntegerValueType(42), new NativeStringType()),
            new ShapedArrayElement(new IntegerValueType(42), new NativeStringType()),
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

    public function test_matches_valid_array_shaped_type(): void
    {
        $otherA = ShapedArrayType::unsealed(
            new ArrayType(ArrayKeyType::string(), new NativeFloatType()),
            new ShapedArrayElement(new StringValueType('foo'), new NativeStringType()),
            new ShapedArrayElement(new IntegerValueType(1337), new NativeIntegerType()),
        );

        $otherB = ShapedArrayType::unsealed(
            new ArrayType(ArrayKeyType::string(), new NativeFloatType()),
            new ShapedArrayElement(new StringValueType('foo'), new NativeStringType()),
        );

        $otherC = ShapedArrayType::unsealedWithoutType(
            new ShapedArrayElement(new StringValueType('foo'), new NativeStringType()),
        );

        self::assertTrue($this->type->matches($otherA));
        self::assertTrue($this->type->matches($otherB));
        self::assertTrue($this->type->matches($otherC));
    }

    public function test_unsealed_shaped_array_matches_non_unsealed_shaped_array(): void
    {
        $unsealedShapedArray = ShapedArrayType::unsealedWithoutType(
            new ShapedArrayElement(new IntegerValueType(42), new NativeStringType()),
        );

        $shapedArray = new ShapedArrayType(
            new ShapedArrayElement(new IntegerValueType(42), new NativeStringType()),
        );

        self::assertTrue($unsealedShapedArray->matches($shapedArray));
    }

    public function test_does_not_match_invalid_array_shaped_type_element(): void
    {
        // Valid unsealed type, invalid shaped array element type
        $otherA = ShapedArrayType::unsealed(
            new ArrayType(ArrayKeyType::string(), new NativeFloatType()),
            new ShapedArrayElement(new StringValueType('foo'), new NativeFloatType()),
        );

        // Valid unsealed type, invalid shaped array element key
        $otherB = ShapedArrayType::unsealed(
            new ArrayType(ArrayKeyType::string(), new NativeFloatType()),
            new ShapedArrayElement(new StringValueType('bar'), new NativeStringType()),
        );

        // Valid unsealed type, missing required element
        $otherC = ShapedArrayType::unsealed(
            new ArrayType(ArrayKeyType::string(), new NativeFloatType()),
        );

        // Invalid unsealed type, valid shaped array element
        $otherD = ShapedArrayType::unsealed(
            new ArrayType(ArrayKeyType::string(), new NativeStringType()),
            new ShapedArrayElement(new StringValueType('foo'), new NativeStringType()),
        );

        // Invalid unsealed type key, valid shaped array element
        $otherE = ShapedArrayType::unsealed(
            new ArrayType(ArrayKeyType::integer(), new NativeFloatType()),
            new ShapedArrayElement(new StringValueType('foo'), new NativeStringType()),
        );

        self::assertFalse($this->type->matches($otherA));
        self::assertFalse($this->type->matches($otherB));
        self::assertFalse($this->type->matches($otherC));
        self::assertFalse($this->type->matches($otherD));
        self::assertFalse($this->type->matches($otherE));
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
            ShapedArrayType::unsealed(
                new ArrayType(ArrayKeyType::integer(), new NonEmptyStringType()),
                new ShapedArrayElement(new StringValueType('bar'), new NativeStringType()),
            ),
            new FakeType(),
        );

        self::assertFalse($this->type->matches($unionType));
    }

    public function test_does_not_match_union_containing_invalid_unsealed_type(): void
    {
        $unionType = new UnionType(
            new FakeType(),
            ShapedArrayType::unsealed(
                new ArrayType(ArrayKeyType::string(), new NativeStringType()),
                new ShapedArrayElement(new StringValueType('foo'), new NativeStringType()),
            ),
            new FakeType(),
        );

        self::assertFalse($this->type->matches($unionType));
    }

    public function test_traverse_type_yields_sub_types(): void
    {
        $unsealedType = new ArrayType(ArrayKeyType::integer(), new NonEmptyStringType());
        $subTypeA = new FakeType();
        $subTypeB = new FakeType();

        $type = ShapedArrayType::unsealed(
            $unsealedType,
            new ShapedArrayElement(new StringValueType('foo'), $subTypeA),
            new ShapedArrayElement(new StringValueType('bar'), $subTypeB),
        );

        self::assertCount(4, $type->traverse());
        self::assertContains($unsealedType, $type->traverse());
        self::assertContains($subTypeA, $type->traverse());
        self::assertContains($subTypeB, $type->traverse());
    }

    public function test_traverse_type_yields_types_recursively(): void
    {
        $unsealedSubType = new NonEmptyStringType();
        $unsealedType = new ArrayType(ArrayKeyType::integer(), ArrayType::simple($unsealedSubType));
        $subTypeA = new FakeType();
        $subTypeB = new FakeType();
        $compositeTypeA = new FakeCompositeType($subTypeA);
        $compositeTypeB = new FakeCompositeType($subTypeB);

        $type = ShapedArrayType::unsealed(
            $unsealedType,
            new ShapedArrayElement(new StringValueType('foo'), $compositeTypeA),
            new ShapedArrayElement(new StringValueType('bar'), $compositeTypeB),
        );

        self::assertCount(7, $type->traverse());
        self::assertContains($unsealedType, $type->traverse());
        self::assertContains($unsealedSubType, $type->traverse());
        self::assertContains($subTypeA, $type->traverse());
        self::assertContains($subTypeB, $type->traverse());
        self::assertContains($compositeTypeA, $type->traverse());
        self::assertContains($compositeTypeB, $type->traverse());
    }

    private function compiledAccept(Type $type, mixed $value): bool
    {
        return eval('return ' . $type->compiledAccept(Node::variable('value'))->compile(new Compiler())->code() . ';');
    }
}
