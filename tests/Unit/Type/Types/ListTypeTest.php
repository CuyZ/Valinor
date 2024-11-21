<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Types;

use CuyZ\Valinor\Tests\Fake\Type\FakeCompositeType;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Type\Types\ArrayKeyType;
use CuyZ\Valinor\Type\Types\ArrayType;
use CuyZ\Valinor\Type\Types\IterableType;
use CuyZ\Valinor\Type\Types\ListType;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\NativeStringType;
use CuyZ\Valinor\Type\Types\UnionType;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use stdClass;

final class ListTypeTest extends TestCase
{
    public function test_sub_type_can_be_retrieved(): void
    {
        $subType = NativeStringType::get();

        $listType = new ListType($subType);

        self::assertSame(ArrayKeyType::integer(), $listType->keyType());
        self::assertSame($subType, $listType->subType());
    }

    public function test_native_string_value_is_correct(): void
    {
        self::assertSame('list', ListType::native()->toString());
    }

    public function test_native_returns_same_instance(): void
    {
        self::assertSame(ListType::native(), ListType::native());
    }

    public function test_native_subtype_is_correct(): void
    {
        self::assertInstanceOf(MixedType::class, ListType::native()->subType());
    }

    public function test_string_value_is_correct(): void
    {
        $subType = new FakeType();

        self::assertSame("list<{$subType->toString()}>", (new ListType($subType))->toString());
    }

    public function test_subtype_is_correct(): void
    {
        $subType = new FakeType();

        self::assertSame($subType, (new ListType($subType))->subType());
    }

    #[TestWith(['accepts' => true, 'value' => []])]
    #[TestWith(['accepts' => true, 'value' => ['Some value', 'Some value', 'Some value']])]
    #[TestWith(['accepts' => true, 'value' => ['Some value', 'Schwifty!', 'Some value']])]
    #[TestWith(['accepts' => false, 'value' => [1 => 'Some value', 2 => 'Some value']])]
    public function test_native_list_type_accepts_correct_values(bool $accepts, mixed $value): void
    {
        self::assertSame($accepts, ListType::native()->accepts($value));
    }

    #[TestWith(['accepts' => true, 'value' => []])]
    #[TestWith(['accepts' => true, 'value' => ['Some value', 'Some value', 'Some value']])]
    #[TestWith(['accepts' => false, 'value' => ['Some value', 'Schwifty!', 'Some value']])]
    #[TestWith(['accepts' => false, 'value' => [1 => 'Some value', 2 => 'Some value']])]
    public function test_list_of_type_accepts_correct_values(bool $accepts, mixed $value): void
    {
        $type = new ListType(FakeType::accepting('Some value'));

        self::assertSame($accepts, $type->accepts($value));
    }

    #[TestWith([[1 => 'foo']])]
    #[TestWith([['foo' => 'foo']])]
    #[TestWith([null])]
    #[TestWith(['Schwifty!'])]
    #[TestWith([42.1337])]
    #[TestWith([404])]
    #[TestWith([false])]
    #[TestWith([new stdClass()])]
    public function test_does_not_accept_incorrect_values(mixed $value): void
    {
        self::assertFalse(ListType::native()->accepts($value));
        self::assertFalse((new ListType(FakeType::accepting('Some value')))->accepts($value));
    }

    public function test_matches_valid_list_type(): void
    {
        $typeA = FakeType::matching($typeB = new FakeType());

        $listOfTypeA = new ListType($typeA);
        $listOfTypeB = new ListType($typeB);

        self::assertTrue($listOfTypeA->matches($listOfTypeB));
    }

    public function test_does_not_match_invalid_list_type(): void
    {
        $typeA = new FakeType();
        $typeB = new FakeType();

        $listOfTypeA = new ListType($typeA);
        $listOfTypeB = new ListType($typeB);

        self::assertFalse($listOfTypeA->matches($listOfTypeB));
    }

    public function test_matches_valid_array_type(): void
    {
        $typeA = FakeType::matching($typeB = new FakeType());

        $listType = new ListType($typeA);
        $arrayType = new ArrayType(ArrayKeyType::integer(), $typeB);

        self::assertTrue($listType->matches($arrayType));
    }

    public function test_does_not_match_invalid_array_type(): void
    {
        $typeA = new FakeType();
        $typeB = new FakeType();

        $listType = new ListType($typeA);
        $arrayTypeWithInvalidSubtype = new ArrayType(ArrayKeyType::integer(), $typeB);
        $arrayTypeWithInvalidKeyType = new ArrayType(ArrayKeyType::string(), $typeA);

        self::assertFalse($listType->matches($arrayTypeWithInvalidSubtype));
        self::assertFalse($listType->matches($arrayTypeWithInvalidKeyType));
    }

    public function test_matches_valid_iterable_type(): void
    {
        $typeA = FakeType::matching($typeB = new FakeType());

        $listType = new ListType($typeA);
        $iterableType = new IterableType(ArrayKeyType::integer(), $typeB);

        self::assertTrue($listType->matches($iterableType));
    }

    public function test_does_not_match_invalid_iterable_type(): void
    {
        $typeA = new FakeType();
        $typeB = new FakeType();

        $listType = new ListType($typeA);
        $iterableTypeWithInvalidSubtype = new IterableType(ArrayKeyType::integer(), $typeB);
        $iterableTypeWithInvalidKeyType = new IterableType(ArrayKeyType::string(), $typeA);

        self::assertFalse($listType->matches($iterableTypeWithInvalidSubtype));
        self::assertFalse($listType->matches($iterableTypeWithInvalidKeyType));
    }

    public function test_does_not_match_other_type(): void
    {
        $typeA = new FakeType();
        $typeB = new FakeType();

        self::assertFalse((new ListType($typeA))->matches($typeB));
    }

    public function test_matches_mixed_type(): void
    {
        self::assertTrue(ListType::native()->matches(new MixedType()));
        self::assertTrue((new ListType(new FakeType()))->matches(new MixedType()));
    }

    public function test_matches_union_containing_valid_type(): void
    {
        $unionType = new UnionType(
            new FakeType(),
            ListType::native(),
            new FakeType(),
        );

        self::assertTrue((new ListType(new FakeType()))->matches($unionType));
    }

    public function test_does_not_match_union_containing_invalid_type(): void
    {
        $unionType = new UnionType(
            new FakeType(),
            new ListType(new FakeType()),
            new FakeType(),
        );

        self::assertFalse(ListType::native()->matches($unionType));
    }

    public function test_traverse_type_yields_sub_type(): void
    {
        $subType = new FakeType();

        $type = new ListType($subType);

        self::assertCount(1, $type->traverse());
        self::assertContains($subType, $type->traverse());
    }

    public function test_traverse_type_yields_types_recursively(): void
    {
        $subType = new FakeType();
        $compositeType = new FakeCompositeType($subType);

        $type = new ListType($compositeType);

        self::assertCount(2, $type->traverse());
        self::assertContains($subType, $type->traverse());
        self::assertContains($compositeType, $type->traverse());
    }
}
