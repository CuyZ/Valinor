<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Types;

use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Type\Types\ArrayKeyType;
use CuyZ\Valinor\Type\Types\ArrayType;
use CuyZ\Valinor\Type\Types\IterableType;
use CuyZ\Valinor\Type\Types\ListType;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\NativeStringType;
use CuyZ\Valinor\Type\Types\UnionType;
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
        self::assertSame('list', (string)ListType::native());
    }

    public function test_native_returns_same_instance(): void
    {
        self::assertSame(ListType::native(), ListType::native()); // @phpstan-ignore-line
    }

    public function test_native_subtype_is_correct(): void
    {
        self::assertInstanceOf(MixedType::class, ListType::native()->subType());
    }

    public function test_string_value_is_correct(): void
    {
        $subType = new FakeType();

        self::assertSame("list<$subType>", (string)new ListType($subType));
    }

    public function test_subtype_is_correct(): void
    {
        $subType = new FakeType();

        self::assertSame($subType, (new ListType($subType))->subType());
    }

    public function test_accepts_correct_values(): void
    {
        $type = FakeType::accepting('Some value');

        self::assertTrue((new ListType($type))->accepts([
            'Some value',
            'Some value',
            'Some value',
        ]));

        self::assertFalse((new ListType($type))->accepts([
            'Some value',
            'Schwifty!',
            'Some value',
        ]));
    }

    public function test_does_not_accept_incorrect_values(): void
    {
        self::assertFalse(ListType::native()->accepts([1 => 'foo']));
        self::assertFalse(ListType::native()->accepts(['foo' => 'foo']));

        self::assertFalse(ListType::native()->accepts(null));
        self::assertFalse(ListType::native()->accepts('Schwifty!'));
        self::assertFalse(ListType::native()->accepts(42.1337));
        self::assertFalse(ListType::native()->accepts(404));
        self::assertFalse(ListType::native()->accepts(false));
        self::assertFalse(ListType::native()->accepts(new stdClass()));
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
}
