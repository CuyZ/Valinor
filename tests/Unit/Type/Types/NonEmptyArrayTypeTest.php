<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Types;

use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Type\Types\ArrayKeyType;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\NativeStringType;
use CuyZ\Valinor\Type\Types\NonEmptyArrayType;
use CuyZ\Valinor\Type\Types\UnionType;
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
        self::assertSame('non-empty-array', (string)NonEmptyArrayType::native());
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

        self::assertSame("non-empty-array<$subType>", (string)new NonEmptyArrayType(ArrayKeyType::default(), $subType));
    }

    public function test_subtype_is_correct(): void
    {
        $subType = new FakeType();

        self::assertSame($subType, (new NonEmptyArrayType(ArrayKeyType::default(), $subType))->subType());
    }

    public function test_accepts_correct_values(): void
    {
        $type = FakeType::accepting('Some value');

        $arrayWithDefaultKey = new NonEmptyArrayType(ArrayKeyType::default(), $type);
        $arrayWithIntegerKey = new NonEmptyArrayType(ArrayKeyType::integer(), $type);
        $arrayWithStringKey = new NonEmptyArrayType(ArrayKeyType::string(), $type);

        self::assertTrue($arrayWithDefaultKey->accepts([42 => 'Some value']));
        self::assertTrue($arrayWithDefaultKey->accepts(['foo' => 'Some value']));
        self::assertFalse($arrayWithDefaultKey->accepts([42 => 1337.404]));
        self::assertFalse($arrayWithDefaultKey->accepts(['foo' => 1337.404]));

        self::assertTrue($arrayWithIntegerKey->accepts([42 => 'Some value']));
        self::assertFalse($arrayWithIntegerKey->accepts(['foo' => 'Some value']));
        self::assertFalse($arrayWithIntegerKey->accepts([42 => 1337.404]));

        self::assertTrue($arrayWithStringKey->accepts([42 => 'Some value']));
        self::assertTrue($arrayWithStringKey->accepts(['foo' => 'Some value']));
        self::assertFalse($arrayWithIntegerKey->accepts([42 => 1337.404]));

        self::assertFalse(NonEmptyArrayType::native()->accepts('foo'));
    }

    public function test_does_not_accept_incorrect_values(): void
    {
        self::assertFalse(NonEmptyArrayType::native()->accepts([]));
        self::assertFalse(NonEmptyArrayType::native()->accepts(null));
        self::assertFalse(NonEmptyArrayType::native()->accepts('Schwifty!'));
        self::assertFalse(NonEmptyArrayType::native()->accepts(42.1337));
        self::assertFalse(NonEmptyArrayType::native()->accepts(404));
        self::assertFalse(NonEmptyArrayType::native()->accepts(false));
        self::assertFalse(NonEmptyArrayType::native()->accepts(new stdClass()));
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
}
