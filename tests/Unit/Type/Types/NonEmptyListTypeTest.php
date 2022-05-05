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
use CuyZ\Valinor\Type\Types\NonEmptyArrayType;
use CuyZ\Valinor\Type\Types\NonEmptyListType;
use CuyZ\Valinor\Type\Types\UnionType;
use PHPUnit\Framework\TestCase;
use stdClass;

final class NonEmptyListTypeTest extends TestCase
{
    public function test_sub_type_can_be_retrieved(): void
    {
        $subType = NativeStringType::get();

        $listType = new NonEmptyListType($subType);

        self::assertSame(ArrayKeyType::integer(), $listType->keyType());
        self::assertSame($subType, $listType->subType());
    }

    public function test_native_string_value_is_correct(): void
    {
        self::assertSame('non-empty-list', (string)NonEmptyListType::native());
    }

    public function test_native_returns_same_instance(): void
    {
        self::assertSame(NonEmptyListType::native(), NonEmptyListType::native());
    }

    public function test_native_subtype_is_correct(): void
    {
        self::assertInstanceOf(MixedType::class, NonEmptyListType::native()->subType());
    }

    public function test_string_value_is_correct(): void
    {
        $subType = new FakeType();

        self::assertSame("non-empty-list<$subType>", (string)new NonEmptyListType($subType));
    }

    public function test_subtype_is_correct(): void
    {
        $subType = new FakeType();

        self::assertSame($subType, (new NonEmptyListType($subType))->subType());
    }

    public function test_accepts_correct_values(): void
    {
        $type = FakeType::accepting('Some value');

        self::assertTrue((new NonEmptyListType($type))->accepts([
            'Some value',
            'Some value',
            'Some value',
        ]));

        self::assertFalse((new NonEmptyListType($type))->accepts([
            'Some value',
            'Schwifty!',
            'Some value',
        ]));
    }

    public function test_does_not_accept_incorrect_values(): void
    {
        self::assertFalse(NonEmptyListType::native()->accepts([]));
        self::assertFalse(NonEmptyListType::native()->accepts([1 => 'foo']));
        self::assertFalse(NonEmptyListType::native()->accepts(['foo' => 'foo']));

        self::assertFalse(NonEmptyListType::native()->accepts(null));
        self::assertFalse(NonEmptyListType::native()->accepts('Schwifty!'));
        self::assertFalse(NonEmptyListType::native()->accepts(42.1337));
        self::assertFalse(NonEmptyListType::native()->accepts(404));
        self::assertFalse(NonEmptyListType::native()->accepts(false));
        self::assertFalse(NonEmptyListType::native()->accepts(new stdClass()));
    }

    public function test_matches_valid_list_type(): void
    {
        $typeA = FakeType::matching($typeB = new FakeType());

        $listOfTypeA = new NonEmptyListType($typeA);
        $listOfTypeB = new ListType($typeB);

        self::assertTrue($listOfTypeA->matches($listOfTypeB));
    }

    public function test_does_not_match_invalid_list_type(): void
    {
        $typeA = new FakeType();
        $typeB = new FakeType();

        $listOfTypeA = new NonEmptyListType($typeA);
        $listOfTypeB = new ListType($typeB);

        self::assertFalse($listOfTypeA->matches($listOfTypeB));
    }

    public function test_matches_valid_non_empty_list_type(): void
    {
        $typeA = FakeType::matching($typeB = new FakeType());

        $listOfTypeA = new NonEmptyListType($typeA);
        $listOfTypeB = new NonEmptyListType($typeB);

        self::assertTrue($listOfTypeA->matches($listOfTypeB));
    }

    public function test_does_not_match_invalid_non_empty_list_type(): void
    {
        $typeA = new FakeType();
        $typeB = new FakeType();

        $listOfTypeA = new NonEmptyListType($typeA);
        $listOfTypeB = new NonEmptyListType($typeB);

        self::assertFalse($listOfTypeA->matches($listOfTypeB));
    }

    public function test_matches_valid_array_type(): void
    {
        $typeA = FakeType::matching($typeB = new FakeType());

        $listType = new NonEmptyListType($typeA);
        $arrayType = new ArrayType(ArrayKeyType::integer(), $typeB);

        self::assertTrue($listType->matches($arrayType));
    }

    public function test_does_not_match_invalid_array_type(): void
    {
        $typeA = new FakeType();
        $typeB = new FakeType();

        $listType = new NonEmptyListType($typeA);
        $arrayTypeWithInvalidSubtype = new ArrayType(ArrayKeyType::integer(), $typeB);
        $arrayTypeWithInvalidKeyType = new ArrayType(ArrayKeyType::string(), $typeA);

        self::assertFalse($listType->matches($arrayTypeWithInvalidSubtype));
        self::assertFalse($listType->matches($arrayTypeWithInvalidKeyType));
    }

    public function test_matches_valid_non_empty_array_type(): void
    {
        $typeA = FakeType::matching($typeB = new FakeType());

        $listType = new NonEmptyListType($typeA);
        $arrayType = new NonEmptyArrayType(ArrayKeyType::integer(), $typeB);

        self::assertTrue($listType->matches($arrayType));
    }

    public function test_does_not_match_invalid_non_empty_array_type(): void
    {
        $typeA = new FakeType();
        $typeB = new FakeType();

        $listType = new NonEmptyListType($typeA);
        $arrayTypeWithInvalidSubtype = new NonEmptyArrayType(ArrayKeyType::integer(), $typeB);
        $arrayTypeWithInvalidKeyType = new NonEmptyArrayType(ArrayKeyType::string(), $typeA);

        self::assertFalse($listType->matches($arrayTypeWithInvalidSubtype));
        self::assertFalse($listType->matches($arrayTypeWithInvalidKeyType));
    }

    public function test_matches_valid_iterable_type(): void
    {
        $typeA = FakeType::matching($typeB = new FakeType());

        $listType = new NonEmptyListType($typeA);
        $iterableType = new IterableType(ArrayKeyType::integer(), $typeB);

        self::assertTrue($listType->matches($iterableType));
    }

    public function test_does_not_match_invalid_iterable_type(): void
    {
        $typeA = new FakeType();
        $typeB = new FakeType();

        $listType = new NonEmptyListType($typeA);
        $iterableTypeWithInvalidSubtype = new IterableType(ArrayKeyType::integer(), $typeB);
        $iterableTypeWithInvalidKeyType = new IterableType(ArrayKeyType::string(), $typeA);

        self::assertFalse($listType->matches($iterableTypeWithInvalidSubtype));
        self::assertFalse($listType->matches($iterableTypeWithInvalidKeyType));
    }

    public function test_does_not_match_other_type(): void
    {
        $typeA = new FakeType();
        $typeB = new FakeType();

        self::assertFalse((new NonEmptyListType($typeA))->matches($typeB));
    }

    public function test_matches_mixed_type(): void
    {
        self::assertTrue(NonEmptyListType::native()->matches(new MixedType()));
        self::assertTrue((new NonEmptyListType(new FakeType()))->matches(new MixedType()));
    }

    public function test_matches_union_containing_valid_type(): void
    {
        $unionType = new UnionType(
            new FakeType(),
            NonEmptyListType::native(),
            new FakeType(),
        );

        self::assertTrue((new NonEmptyListType(new FakeType()))->matches($unionType));
    }

    public function test_does_not_match_union_containing_invalid_type(): void
    {
        $unionType = new UnionType(
            new FakeType(),
            new NonEmptyListType(new FakeType()),
            new FakeType(),
        );

        self::assertFalse(NonEmptyListType::native()->matches($unionType));
    }

    public function test_traverse_type_yields_sub_type(): void
    {
        $subType = new FakeType();

        $type = new NonEmptyListType($subType);

        self::assertCount(1, $type->traverse());
        self::assertContains($subType, $type->traverse());
    }

    public function test_traverse_type_yields_types_recursively(): void
    {
        $subType = new FakeType();
        $compositeType = new FakeCompositeType($subType);

        $type = new NonEmptyListType($compositeType);

        self::assertCount(2, $type->traverse());
        self::assertContains($subType, $type->traverse());
        self::assertContains($compositeType, $type->traverse());
    }
}
