<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Types;

use CuyZ\Valinor\Tests\Fake\Type\FakeCompositeType;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Type\Types\ArrayKeyType;
use CuyZ\Valinor\Type\Types\ArrayType;
use CuyZ\Valinor\Type\Types\IterableType;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\NativeStringType;
use CuyZ\Valinor\Type\Types\UnionType;
use PHPUnit\Framework\TestCase;
use stdClass;

final class ArrayTypeTest extends TestCase
{
    public function test_key_and_sub_type_can_be_retrieved(): void
    {
        $keyType = ArrayKeyType::default();
        $subType = NativeStringType::get();

        $arrayType = new ArrayType($keyType, $subType);

        self::assertSame($keyType, $arrayType->keyType());
        self::assertSame($subType, $arrayType->subType());
    }

    public function test_native_string_value_is_correct(): void
    {
        self::assertSame('array', ArrayType::native()->toString());
    }

    public function test_native_returns_same_instance(): void
    {
        self::assertSame(ArrayType::native(), ArrayType::native());
    }

    public function test_native_subtype_is_correct(): void
    {
        self::assertInstanceOf(MixedType::class, ArrayType::native()->subType());
    }

    public function test_string_value_is_correct(): void
    {
        $subType = new FakeType();

        self::assertSame("array<{$subType->toString()}>", (new ArrayType(ArrayKeyType::default(), $subType))->toString());
    }

    public function test_subtype_is_correct(): void
    {
        $subType = new FakeType();

        self::assertSame($subType, (new ArrayType(ArrayKeyType::default(), $subType))->subType());
    }

    public function test_simple_array_string_value_is_correct(): void
    {
        $subType = new FakeType();

        self::assertSame($subType->toString() . '[]', ArrayType::simple($subType)->toString());
    }

    public function test_simple_array_subtype_is_correct(): void
    {
        $subType = new FakeType();

        self::assertSame($subType, ArrayType::simple($subType)->subType());
    }

    public function test_accepts_correct_values(): void
    {
        $type = FakeType::accepting('Some value');

        $arrayWithDefaultKey = new ArrayType(ArrayKeyType::default(), $type);
        $arrayWithIntegerKey = new ArrayType(ArrayKeyType::integer(), $type);
        $arrayWithStringKey = new ArrayType(ArrayKeyType::string(), $type);

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

        self::assertFalse(ArrayType::native()->accepts('foo'));
    }

    public function test_does_not_accept_incorrect_values(): void
    {
        self::assertFalse(ArrayType::native()->accepts(null));
        self::assertFalse(ArrayType::native()->accepts('Schwifty!'));
        self::assertFalse(ArrayType::native()->accepts(42.1337));
        self::assertFalse(ArrayType::native()->accepts(404));
        self::assertFalse(ArrayType::native()->accepts(false));
        self::assertFalse(ArrayType::native()->accepts(new stdClass()));
    }

    public function test_matches_valid_array_type(): void
    {
        $typeA = FakeType::matching($typeB = new FakeType());

        $arrayOfTypeA = new ArrayType(ArrayKeyType::default(), $typeA);
        $arrayOfTypeB = new ArrayType(ArrayKeyType::default(), $typeB);

        self::assertTrue($arrayOfTypeA->matches($arrayOfTypeB));
    }

    public function test_does_not_match_invalid_array_type(): void
    {
        $typeA = new FakeType();
        $typeB = new FakeType();

        $arrayOfTypeA = new ArrayType(ArrayKeyType::default(), $typeA);
        $arrayOfTypeB = new ArrayType(ArrayKeyType::default(), $typeB);

        self::assertFalse($arrayOfTypeA->matches($arrayOfTypeB));
    }

    public function test_matches_valid_iterable_type(): void
    {
        $typeA = FakeType::matching($typeB = new FakeType());

        $arrayType = new ArrayType(ArrayKeyType::default(), $typeA);
        $iterableType = new IterableType(ArrayKeyType::default(), $typeB);

        self::assertTrue($arrayType->matches($iterableType));
    }

    public function test_does_not_match_invalid_iterable_type(): void
    {
        $typeA = new FakeType();
        $typeB = new FakeType();

        $arrayType = new ArrayType(ArrayKeyType::default(), $typeA);
        $iterableType = new IterableType(ArrayKeyType::default(), $typeB);

        self::assertFalse($arrayType->matches($iterableType));
    }

    public function test_does_not_match_other_type(): void
    {
        $typeA = new FakeType();
        $typeB = new FakeType();

        self::assertFalse((new ArrayType(ArrayKeyType::default(), $typeA))->matches($typeB));
    }

    public function test_matches_mixed_type(): void
    {
        self::assertTrue(ArrayType::native()->matches(new MixedType()));
        self::assertTrue((new ArrayType(ArrayKeyType::default(), new FakeType()))->matches(new MixedType()));
    }

    public function test_matches_union_containing_valid_type(): void
    {
        $unionType = new UnionType(
            new FakeType(),
            ArrayType::native(),
            new FakeType(),
        );

        self::assertTrue((new ArrayType(ArrayKeyType::default(), new FakeType()))->matches($unionType));
    }

    public function test_does_not_match_union_containing_invalid_type(): void
    {
        $unionType = new UnionType(
            new FakeType(),
            new ArrayType(ArrayKeyType::default(), new FakeType()),
            new FakeType(),
        );

        self::assertFalse(ArrayType::native()->matches($unionType));
    }

    public function test_traverse_type_yields_sub_type(): void
    {
        $subType = new FakeType();

        $type = new ArrayType(ArrayKeyType::default(), $subType);

        self::assertCount(1, $type->traverse());
        self::assertContains($subType, $type->traverse());
    }

    public function test_traverse_type_yields_types_recursively(): void
    {
        $subType = new FakeType();
        $compositeType = new FakeCompositeType($subType);

        $type = new ArrayType(ArrayKeyType::default(), $compositeType);

        self::assertCount(2, $type->traverse());
        self::assertContains($subType, $type->traverse());
        self::assertContains($compositeType, $type->traverse());
    }
}
