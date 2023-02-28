<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Types;

use CuyZ\Valinor\Tests\Fake\Type\FakeCompositeType;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\ShapedArrayElementDuplicatedKey;
use CuyZ\Valinor\Type\Types\ArrayKeyType;
use CuyZ\Valinor\Type\Types\ArrayType;
use CuyZ\Valinor\Type\Types\NativeFloatType;
use CuyZ\Valinor\Type\Types\IntegerValueType;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\NativeIntegerType;
use CuyZ\Valinor\Type\Types\NativeStringType;
use CuyZ\Valinor\Type\Types\ShapedArrayElement;
use CuyZ\Valinor\Type\Types\ShapedArrayType;
use CuyZ\Valinor\Type\Types\StringValueType;
use CuyZ\Valinor\Type\Types\UnionType;
use PHPUnit\Framework\TestCase;
use stdClass;

final class ShapedArrayTypeTest extends TestCase
{
    /** @var ShapedArrayElement[] */
    private array $elements;

    private ShapedArrayType $type;

    protected function setUp(): void
    {
        parent::setUp();

        $this->elements = [
            new ShapedArrayElement(new StringValueType('foo'), new NativeStringType()),
            new ShapedArrayElement(new IntegerValueType(1337), new NativeIntegerType(), true),
        ];

        $this->type = new ShapedArrayType(true, ...$this->elements);
    }

    public function test_shape_elements_can_be_retrieved(): void
    {
        self::assertSame($this->elements, $this->type->elements());
    }

    public function test_duplicate_element_key_throws_exception(): void
    {
        $this->expectException(ShapedArrayElementDuplicatedKey::class);
        $this->expectExceptionCode(1631283279);
        $this->expectExceptionMessage('Key `foo` cannot be used several times in shaped array signature `array{foo: string, foo: string}`.');

        new ShapedArrayType(
            true,
            new ShapedArrayElement(new StringValueType('foo'), new NativeStringType()),
            new ShapedArrayElement(new StringValueType('foo'), new NativeStringType()),
        );
    }

    public function test_string_value_is_correct(): void
    {
        self::assertSame('array{foo: string, 1337?: int}', $this->type->toString());
    }

    public function test_accepts_correct_values(): void
    {
        self::assertTrue($this->type->accepts(['foo' => 'foo', 1337 => 42]));
        self::assertTrue($this->type->accepts(['foo' => 'foo']));
    }

    public function test_does_not_accept_incorrect_values(): void
    {
        self::assertFalse($this->type->accepts(['foo' => 42]));
        self::assertFalse($this->type->accepts(['foo' => new stdClass()]));
        self::assertFalse($this->type->accepts(['bar' => 'foo']));

        self::assertFalse($this->type->accepts(null));
        self::assertFalse($this->type->accepts('Schwifty!'));
        self::assertFalse($this->type->accepts(42.1337));
        self::assertFalse($this->type->accepts(404));
        self::assertFalse($this->type->accepts(false));
        self::assertFalse($this->type->accepts(new stdClass()));
    }

    public function test_does_not_accept_array_with_excessive_keys(): void
    {
        self::assertFalse($this->type->accepts(['foo' => 'foo', 'fiz' => 42]));
    }

    public function test_matches_valid_array_shaped_type(): void
    {
        $otherA = new ShapedArrayType(
            true,
            new ShapedArrayElement(new StringValueType('foo'), new NativeStringType()),
            new ShapedArrayElement(new IntegerValueType(42), new NativeIntegerType()),
        );

        $otherB = new ShapedArrayType(
            true,
            new ShapedArrayElement(new StringValueType('foo'), new NativeStringType())
        );

        self::assertTrue($this->type->matches($otherA));
        self::assertTrue($this->type->matches($otherB));
    }

    public function test_does_not_match_invalid_array_shaped_type(): void
    {
        $otherA = new ShapedArrayType(
            true,
            new ShapedArrayElement(new StringValueType('foo'), new NativeFloatType()),
        );

        $otherB = new ShapedArrayType(
            true,
            new ShapedArrayElement(new StringValueType('bar'), new NativeStringType())
        );

        self::assertFalse($this->type->matches($otherA));
        self::assertFalse($this->type->matches($otherB));
    }

    public function test_matches_valid_generic_array_type(): void
    {
        $other = new ArrayType(ArrayKeyType::default(), new UnionType(new NativeStringType(), new NativeIntegerType()));

        self::assertTrue($this->type->matches($other));
    }

    public function test_does_not_match_invalid_generic_array_type(): void
    {
        $otherA = new ArrayType(ArrayKeyType::string(), new UnionType(new NativeStringType(), new NativeIntegerType()));
        $otherB = new ArrayType(ArrayKeyType::integer(), new UnionType(new NativeStringType(), new NativeIntegerType()));
        $otherC = new ArrayType(ArrayKeyType::default(), new NativeStringType());
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

    public function test_does_not_match_union_containing_invalid_type(): void
    {
        $unionType = new UnionType(
            new FakeType(),
            new ShapedArrayType(
                true,
                new ShapedArrayElement(new StringValueType('bar'), new NativeStringType())
            ),
            new FakeType(),
        );

        self::assertFalse($this->type->matches($unionType));
    }

    public function test_traverse_type_yields_sub_types(): void
    {
        $subTypeA = new FakeType();
        $subTypeB = new FakeType();

        $type = new ShapedArrayType(
            true,
            new ShapedArrayElement(new StringValueType('foo'), $subTypeA),
            new ShapedArrayElement(new StringValueType('bar'), $subTypeB),
        );

        self::assertCount(2, $type->traverse());
        self::assertContains($subTypeA, $type->traverse());
        self::assertContains($subTypeB, $type->traverse());
    }

    public function test_traverse_type_yields_types_recursively(): void
    {
        $subTypeA = new FakeType();
        $subTypeB = new FakeType();
        $compositeTypeA = new FakeCompositeType($subTypeA);
        $compositeTypeB = new FakeCompositeType($subTypeB);

        $type = new ShapedArrayType(
            true,
            new ShapedArrayElement(new StringValueType('foo'), $compositeTypeA),
            new ShapedArrayElement(new StringValueType('bar'), $compositeTypeB),
        );

        self::assertCount(4, $type->traverse());
        self::assertContains($subTypeA, $type->traverse());
        self::assertContains($subTypeB, $type->traverse());
        self::assertContains($compositeTypeA, $type->traverse());
        self::assertContains($compositeTypeB, $type->traverse());
    }
}
