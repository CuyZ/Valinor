<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Types;

use CuyZ\Valinor\Tests\Fake\Type\FakeCompositeType;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Type\Types\ArrayKeyType;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\NativeStringType;
use CuyZ\Valinor\Type\Types\NonEmptyArrayType;
use CuyZ\Valinor\Type\Types\StringValueType;
use CuyZ\Valinor\Type\Types\UnionType;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use stdClass;

final class NonEmptyArrayTypeTest extends TestCase
{
    public function test_key_and_sub_type_can_be_retrieved(): void
    {
        $keyType = ArrayKeyType::default();
        $subType = NativeStringType::get();

        $arrayType = new NonEmptyArrayType($keyType, $subType);

        self::assertSame($keyType, $arrayType->keyType());
        self::assertSame($subType, $arrayType->subType());
    }

    public function test_native_string_value_is_correct(): void
    {
        self::assertSame('non-empty-array', NonEmptyArrayType::native()->toString());
    }

    public function test_native_returns_same_instance(): void
    {
        self::assertSame(NonEmptyArrayType::native(), NonEmptyArrayType::native());
    }

    public function test_native_subtype_is_correct(): void
    {
        self::assertInstanceOf(MixedType::class, NonEmptyArrayType::native()->subType());
    }

    public function test_string_value_is_correct(): void
    {
        $subType = new FakeType();

        self::assertSame("non-empty-array<{$subType->toString()}>", (new NonEmptyArrayType(ArrayKeyType::default(), $subType))->toString());
    }

    public function test_subtype_is_correct(): void
    {
        $subType = new FakeType();

        self::assertSame($subType, (new NonEmptyArrayType(ArrayKeyType::default(), $subType))->subType());
    }

    #[TestWith(['accepts' => true, 'value' => [42 => 'Some value']])]
    #[TestWith(['accepts' => true, 'value' => ['foo' => 'Some value']])]
    #[TestWith(['accepts' => true, 'value' => [42 => 1337.404]])]
    #[TestWith(['accepts' => true, 'value' => ['foo' => 1337.404]])]
    public function test_native_array_accepts_correct_values(bool $accepts, mixed $value): void
    {
        $type = NonEmptyArrayType::native();

        self::assertSame($accepts, $type->accepts($value));
    }

    #[TestWith(['accepts' => true, 'value' => [42 => 'Some value']])]
    #[TestWith(['accepts' => true, 'value' => ['foo' => 'Some value']])]
    #[TestWith(['accepts' => false, 'value' => [42 => 1337.404]])]
    #[TestWith(['accepts' => false, 'value' => ['foo' => 1337.404]])]
    public function test_default_array_key_accepts_correct_values(bool $accepts, mixed $value): void
    {
        $type = new NonEmptyArrayType(ArrayKeyType::default(), new StringValueType('Some value'));

        self::assertSame($accepts, $type->accepts($value));
    }

    #[TestWith(['accepts' => true, 'value' => [42 => 'Some value']])]
    #[TestWith(['accepts' => false, 'value' => ['foo' => 'Some value']])]
    #[TestWith(['accepts' => false, 'value' => [42 => 1337.404]])]
    #[TestWith(['accepts' => false, 'value' => ['foo' => 1337.404]])]
    public function test_integer_array_key_accepts_correct_values(bool $accepts, mixed $value): void
    {
        $type = new NonEmptyArrayType(ArrayKeyType::integer(), new StringValueType('Some value'));

        self::assertSame($accepts, $type->accepts($value));
    }

    #[TestWith(['accepts' => true, 'value' => [42 => 'Some value']])]
    #[TestWith(['accepts' => true, 'value' => ['foo' => 'Some value']])]
    #[TestWith(['accepts' => false, 'value' => [42 => 1337.404]])]
    #[TestWith(['accepts' => false, 'value' => ['foo' => 1337.404]])]
    public function test_string_array_key_accepts_correct_values(bool $accepts, mixed $value): void
    {
        $type = new NonEmptyArrayType(ArrayKeyType::string(), new StringValueType('Some value'));

        self::assertSame($accepts, $type->accepts($value));
    }

    #[TestWith([[]])]
    #[TestWith([null])]
    #[TestWith(['Schwifty!'])]
    #[TestWith([42.1337])]
    #[TestWith([404])]
    #[TestWith([false])]
    #[TestWith([new stdClass()])]
    public function test_does_not_accept_incorrect_values(mixed $value): void
    {
        self::assertFalse(NonEmptyArrayType::native()->accepts($value));
        self::assertFalse((new NonEmptyArrayType(ArrayKeyType::default(), new StringValueType('Some value')))->accepts($value));
        self::assertFalse((new NonEmptyArrayType(ArrayKeyType::integer(), new StringValueType('Some value')))->accepts($value));
        self::assertFalse((new NonEmptyArrayType(ArrayKeyType::string(), new StringValueType('Some value')))->accepts($value));
    }

    public function test_matches_valid_array_type(): void
    {
        $typeA = FakeType::matching($typeB = new FakeType());

        $arrayOfTypeA = new NonEmptyArrayType(ArrayKeyType::default(), $typeA);
        $arrayOfTypeB = new NonEmptyArrayType(ArrayKeyType::default(), $typeB);
        $nonEmptyArrayOfTypeA = new NonEmptyArrayType(ArrayKeyType::default(), $typeA);
        $nonEmptyArrayOfTypeB = new NonEmptyArrayType(ArrayKeyType::default(), $typeB);

        self::assertTrue($arrayOfTypeA->matches($arrayOfTypeB));
        self::assertTrue($nonEmptyArrayOfTypeA->matches($nonEmptyArrayOfTypeB));
    }

    public function test_does_not_match_invalid_array_type(): void
    {
        $typeA = new FakeType();
        $typeB = new FakeType();

        $arrayOfTypeA = new NonEmptyArrayType(ArrayKeyType::default(), $typeA);
        $arrayOfTypeB = new NonEmptyArrayType(ArrayKeyType::default(), $typeB);

        self::assertFalse($arrayOfTypeA->matches($arrayOfTypeB));
    }

    public function test_does_not_match_other_type(): void
    {
        $typeA = new FakeType();
        $typeB = new FakeType();

        self::assertFalse((new NonEmptyArrayType(ArrayKeyType::default(), $typeA))->matches($typeB));
    }

    public function test_matches_mixed_type(): void
    {
        self::assertTrue(NonEmptyArrayType::native()->matches(new MixedType()));
        self::assertTrue((new NonEmptyArrayType(ArrayKeyType::default(), new FakeType()))->matches(new MixedType()));
    }

    public function test_matches_union_containing_valid_type(): void
    {
        $unionType = new UnionType(
            new FakeType(),
            NonEmptyArrayType::native(),
            new FakeType(),
        );

        self::assertTrue((new NonEmptyArrayType(ArrayKeyType::default(), new FakeType()))->matches($unionType));
    }

    public function test_does_not_match_union_containing_invalid_type(): void
    {
        $unionType = new UnionType(
            new FakeType(),
            new NonEmptyArrayType(ArrayKeyType::default(), new FakeType()),
            new FakeType(),
        );

        self::assertFalse(NonEmptyArrayType::native()->matches($unionType));
    }

    public function test_traverse_type_yields_sub_type(): void
    {
        $subType = new FakeType();

        $type = new NonEmptyArrayType(ArrayKeyType::default(), $subType);

        self::assertCount(1, $type->traverse());
        self::assertContains($subType, $type->traverse());
    }

    public function test_traverse_type_yields_types_recursively(): void
    {
        $subType = new FakeType();
        $compositeType = new FakeCompositeType($subType);

        $type = new NonEmptyArrayType(ArrayKeyType::default(), $compositeType);

        self::assertCount(2, $type->traverse());
        self::assertContains($subType, $type->traverse());
        self::assertContains($compositeType, $type->traverse());
    }
}
