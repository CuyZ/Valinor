<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Types;

use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Type\Types\ArrayKeyType;
use CuyZ\Valinor\Type\Types\NativeStringType;
use CuyZ\Valinor\Type\Types\IterableType;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\UnionType;
use PHPUnit\Framework\TestCase;

final class IterableTypeTest extends TestCase
{
    public function test_key_and_sub_type_can_be_retrieved(): void
    {
        $keyType = ArrayKeyType::default();
        $subType = NativeStringType::get();

        $iterableType = new IterableType($keyType, $subType);

        self::assertSame($keyType, $iterableType->keyType());
        self::assertSame($subType, $iterableType->subType());
    }

    public function test_native_string_value_is_correct(): void
    {
        self::assertSame('iterable', (string)IterableType::native());
    }

    public function test_native_returns_same_instance(): void
    {
        self::assertSame(IterableType::native(), IterableType::native()); // @phpstan-ignore-line
    }

    public function test_native_subtype_is_correct(): void
    {
        self::assertInstanceOf(MixedType::class, IterableType::native()->subType());
    }

    public function test_string_value_is_correct(): void
    {
        $subType = new FakeType();

        self::assertSame("iterable<$subType>", (string)new IterableType(ArrayKeyType::default(), $subType));
    }

    public function test_subtype_is_correct(): void
    {
        $subType = new FakeType();

        self::assertSame($subType, (new IterableType(ArrayKeyType::default(), $subType))->subType());
    }

    public function test_accepts_correct_values(): void
    {
        $type = FakeType::accepting('Some value');

        $iterableWithDefaultKey = new IterableType(ArrayKeyType::default(), $type);
        $iterableWithIntegerKey = new IterableType(ArrayKeyType::integer(), $type);
        $iterableWithStringKey = new IterableType(ArrayKeyType::string(), $type);

        self::assertTrue($iterableWithDefaultKey->accepts([42 => 'Some value']));
        self::assertTrue($iterableWithDefaultKey->accepts(['foo' => 'Some value']));
        self::assertFalse($iterableWithDefaultKey->accepts([42 => 1337.404]));
        self::assertFalse($iterableWithDefaultKey->accepts(['foo' => 1337.404]));
        self::assertTrue($iterableWithDefaultKey->accepts((static fn () => yield 42 => 'Some value')()));
        self::assertTrue($iterableWithDefaultKey->accepts((static fn () => yield 'foo' => 'Some value')()));
        self::assertFalse($iterableWithDefaultKey->accepts((static fn () => yield 42 => 1337.404)()));
        self::assertFalse($iterableWithDefaultKey->accepts((static fn () => yield 'foo' => 1337.404)()));

        self::assertTrue($iterableWithIntegerKey->accepts([42 => 'Some value']));
        self::assertFalse($iterableWithIntegerKey->accepts(['foo' => 'Some value']));
        self::assertFalse($iterableWithIntegerKey->accepts([42 => 1337.404]));
        self::assertTrue($iterableWithIntegerKey->accepts((static fn () => yield 42 => 'Some value')()));
        self::assertFalse($iterableWithIntegerKey->accepts((static fn () => yield 'foo' => 'Some value')()));
        self::assertFalse($iterableWithIntegerKey->accepts((static fn () => yield 42 => 1337.404)()));

        self::assertTrue($iterableWithStringKey->accepts([42 => 'Some value']));
        self::assertTrue($iterableWithStringKey->accepts(['foo' => 'Some value']));
        self::assertFalse($iterableWithIntegerKey->accepts([42 => 1337.404]));
        self::assertTrue($iterableWithStringKey->accepts((static fn () => yield 42 => 'Some value')()));
        self::assertTrue($iterableWithStringKey->accepts((static fn () => yield 'foo' => 'Some value')()));
        self::assertFalse($iterableWithStringKey->accepts((static fn () => yield 42 => 1337.404)()));

        self::assertFalse(IterableType::native()->accepts('foo'));
    }

    public function test_matches_valid_iterable_type(): void
    {
        $typeA = FakeType::matching($typeB = new FakeType());

        $iterableOfTypeA = new IterableType(ArrayKeyType::default(), $typeA);
        $iterableOfTypeB = new IterableType(ArrayKeyType::default(), $typeB);

        self::assertTrue($iterableOfTypeA->matches($iterableOfTypeB));
    }

    public function test_does_not_match_invalid_iterable_type(): void
    {
        $typeA = new FakeType();
        $typeB = new FakeType();

        $iterableOfTypeA = new IterableType(ArrayKeyType::default(), $typeA);
        $iterableOfTypeB = new IterableType(ArrayKeyType::default(), $typeB);

        self::assertFalse($iterableOfTypeA->matches($iterableOfTypeB));
    }

    public function test_does_not_match_other_type(): void
    {
        $typeA = new FakeType();
        $typeB = new FakeType();

        $iterableOfTypeA = new IterableType(ArrayKeyType::default(), $typeA);

        self::assertFalse($iterableOfTypeA->matches($typeB));
    }

    public function test_matches_mixed_type(): void
    {
        self::assertTrue(IterableType::native()->matches(new MixedType()));
        self::assertTrue((new IterableType(ArrayKeyType::default(), new FakeType()))->matches(new MixedType()));
    }

    public function test_matches_union_containing_valid_type(): void
    {
        $unionType = new UnionType(
            new FakeType(),
            IterableType::native(),
            new FakeType(),
        );

        self::assertTrue((new IterableType(ArrayKeyType::default(), new FakeType()))->matches($unionType));
    }

    public function test_does_not_match_union_containing_invalid_type(): void
    {
        $unionType = new UnionType(
            new FakeType(),
            new IterableType(ArrayKeyType::default(), new FakeType()),
            new FakeType(),
        );

        self::assertFalse(IterableType::native()->matches($unionType));
    }
}
