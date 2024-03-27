<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Types;

use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Tests\Fixture\Object\StringableObject;
use CuyZ\Valinor\Type\Types\ArrayKeyType;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\NativeIntegerType;
use CuyZ\Valinor\Type\Types\NativeStringType;
use LogicException;
use PHPUnit\Framework\TestCase;
use stdClass;

final class ArrayKeyTypeTest extends TestCase
{
    public function test_instances_are_memoized(): void
    {
        self::assertSame(ArrayKeyType::default(), ArrayKeyType::default());
        self::assertSame(ArrayKeyType::integer(), ArrayKeyType::integer());
        self::assertSame(ArrayKeyType::string(), ArrayKeyType::string());
        self::assertSame(ArrayKeyType::integer(), ArrayKeyType::from(new NativeIntegerType()));
        self::assertSame(ArrayKeyType::string(), ArrayKeyType::from(new NativeStringType()));
    }

    public function test_string_values_are_correct(): void
    {
        self::assertSame('array-key', ArrayKeyType::default()->toString());
        self::assertSame('int', ArrayKeyType::integer()->toString());
        self::assertSame('string', ArrayKeyType::string()->toString());
    }

    public function test_accepts_correct_values(): void
    {
        $arrayKeyDefault = ArrayKeyType::default();
        $arrayKeyInteger = ArrayKeyType::integer();
        $arrayKeyString = ArrayKeyType::string();

        self::assertTrue($arrayKeyDefault->accepts('foo'));
        self::assertTrue($arrayKeyDefault->accepts(42));

        self::assertFalse($arrayKeyInteger->accepts('foo'));
        self::assertTrue($arrayKeyInteger->accepts(42));

        self::assertTrue($arrayKeyString->accepts('foo'));
        self::assertTrue($arrayKeyString->accepts(42));
    }

    public function test_does_not_accept_incorrect_values(): void
    {
        self::assertFalse(ArrayKeyType::default()->accepts(null));
        self::assertFalse(ArrayKeyType::default()->accepts(42.1337));
        self::assertFalse(ArrayKeyType::default()->accepts(['foo' => 'bar']));
        self::assertFalse(ArrayKeyType::default()->accepts(false));
        self::assertFalse(ArrayKeyType::default()->accepts(new stdClass()));
    }

    public function test_default_array_key_can_cast_numeric_and_string_value(): void
    {
        self::assertTrue(ArrayKeyType::default()->canCast(42.1337));
        self::assertTrue(ArrayKeyType::default()->canCast(404));
        self::assertTrue(ArrayKeyType::default()->canCast('foo'));
        self::assertTrue(ArrayKeyType::default()->canCast(new StringableObject('foo')));
    }

    public function test_default_array_key_cannot_cast_other_values(): void
    {
        self::assertFalse(ArrayKeyType::default()->canCast(null));
        self::assertFalse(ArrayKeyType::default()->canCast(['foo' => 'bar']));
        self::assertFalse(ArrayKeyType::default()->canCast(false));
        self::assertFalse(ArrayKeyType::default()->canCast(new stdClass()));
    }

    public function test_integer_array_key_can_cast_numeric_value(): void
    {
        self::assertTrue(ArrayKeyType::integer()->canCast(42));
    }

    public function test_integer_array_key_cannot_cast_other_values(): void
    {
        self::assertFalse(ArrayKeyType::integer()->canCast(null));
        self::assertFalse(ArrayKeyType::integer()->canCast(42.1337));
        self::assertFalse(ArrayKeyType::integer()->canCast(['foo' => 'bar']));
        self::assertFalse(ArrayKeyType::integer()->canCast('Schwifty!'));
        self::assertFalse(ArrayKeyType::integer()->canCast(false));
        self::assertFalse(ArrayKeyType::integer()->canCast(new stdClass()));
    }

    public function test_string_array_key_can_cast_numeric_and_string_value(): void
    {
        self::assertTrue(ArrayKeyType::string()->canCast(42.1337));
        self::assertTrue(ArrayKeyType::string()->canCast(404));
        self::assertTrue(ArrayKeyType::string()->canCast('foo'));
        self::assertTrue(ArrayKeyType::string()->canCast(new StringableObject('foo')));
    }

    public function test_string_array_key_cannot_cast_other_values(): void
    {
        self::assertFalse(ArrayKeyType::string()->canCast(null));
        self::assertFalse(ArrayKeyType::string()->canCast(['foo' => 'bar']));
        self::assertFalse(ArrayKeyType::string()->canCast(false));
        self::assertFalse(ArrayKeyType::string()->canCast(new stdClass()));
    }

    public function test_cast_value_yields_correct_result(): void
    {
        self::assertSame(42, ArrayKeyType::default()->cast(42));
        self::assertSame('42.1337', ArrayKeyType::default()->cast(42.1337));
        self::assertSame('foo', ArrayKeyType::default()->cast('foo'));
        self::assertSame('foo', ArrayKeyType::default()->cast(new StringableObject('foo')));

        self::assertSame(42, ArrayKeyType::integer()->cast(42));

        self::assertSame('42', ArrayKeyType::string()->cast(42));
        self::assertSame('42.1337', ArrayKeyType::string()->cast(42.1337));
        self::assertSame('foo', ArrayKeyType::string()->cast('foo'));
        self::assertSame('foo', ArrayKeyType::string()->cast(new StringableObject('foo')));
    }

    public function test_cast_invalid_value_throw_exception(): void
    {
        $this->expectException(LogicException::class);

        ArrayKeyType::default()->cast(new stdClass());
    }

    public function test_matches_each_others(): void
    {
        $arrayKeyDefault = ArrayKeyType::default();
        $arrayKeyInteger = ArrayKeyType::integer();
        $arrayKeyString = ArrayKeyType::string();

        self::assertTrue($arrayKeyDefault->matches($arrayKeyDefault));
        self::assertFalse($arrayKeyDefault->matches($arrayKeyInteger));
        self::assertFalse($arrayKeyDefault->matches($arrayKeyString));

        self::assertTrue($arrayKeyInteger->matches($arrayKeyDefault));
        self::assertTrue($arrayKeyInteger->matches($arrayKeyInteger));
        self::assertFalse($arrayKeyInteger->matches($arrayKeyString));

        self::assertTrue($arrayKeyString->matches($arrayKeyDefault));
        self::assertTrue($arrayKeyString->matches($arrayKeyString));
        self::assertFalse($arrayKeyString->matches($arrayKeyInteger));
    }

    public function test_does_not_match_other_type(): void
    {
        self::assertFalse(ArrayKeyType::default()->matches(new FakeType()));
    }

    public function test_matches_mixed_type(): void
    {
        self::assertTrue(ArrayKeyType::default()->matches(new MixedType()));
    }
}
