<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Types;

use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Type\Types\ArrayKeyType;
use CuyZ\Valinor\Type\Types\Exception\CannotCastValue;
use PHPUnit\Framework\TestCase;
use stdClass;

final class ArrayKeyTypeTest extends TestCase
{
    public function test_instances_are_memoized(): void
    {
        self::assertSame(ArrayKeyType::default(), ArrayKeyType::default());
        self::assertSame(ArrayKeyType::integer(), ArrayKeyType::integer());
        self::assertSame(ArrayKeyType::string(), ArrayKeyType::string());
    }

    public function test_string_values_are_correct(): void
    {
        self::assertSame('array-key', (string)ArrayKeyType::default());
        self::assertSame('int', (string)ArrayKeyType::integer());
        self::assertSame('string', (string)ArrayKeyType::string());
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

    public function test_can_cast_correct_values(): void
    {
        $arrayKeyDefault = ArrayKeyType::default();
        $arrayKeyInteger = ArrayKeyType::integer();
        $arrayKeyString = ArrayKeyType::string();

        self::assertTrue($arrayKeyDefault->canCast(42));
        self::assertTrue($arrayKeyDefault->canCast('foo'));

        self::assertTrue($arrayKeyInteger->canCast(42));
        self::assertFalse($arrayKeyInteger->canCast('foo'));

        self::assertTrue($arrayKeyString->canCast(42));
        self::assertTrue($arrayKeyString->canCast('foo'));
    }

    public function test_cast_value_returns_correct_value(): void
    {
        $arrayKeyDefault = ArrayKeyType::default();
        $arrayKeyInteger = ArrayKeyType::integer();
        $arrayKeyString = ArrayKeyType::string();

        self::assertSame(42, $arrayKeyDefault->cast(42));
        self::assertSame('foo', $arrayKeyDefault->cast('foo'));

        self::assertSame(42, $arrayKeyInteger->cast('42'));

        self::assertSame(42, $arrayKeyString->cast(42));
        self::assertSame('foo', $arrayKeyString->cast('foo'));
    }

    public function test_cast_unsupported_value_throws_exception(): void
    {
        $this->expectException(CannotCastValue::class);
        $this->expectExceptionCode(1603216198);
        $this->expectExceptionMessage('Cannot cast object(stdClass) to `array-key`.');

        ArrayKeyType::default()->cast(new stdClass());
    }
}
