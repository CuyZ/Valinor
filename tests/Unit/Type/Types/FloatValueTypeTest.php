<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Types;

use AssertionError;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Type\Types\FloatValueType;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\NativeFloatType;
use CuyZ\Valinor\Type\Types\UnionType;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use stdClass;

final class FloatValueTypeTest extends TestCase
{
    private FloatValueType $floatValueType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->floatValueType = new FloatValueType(1337.42);
    }

    public function test_value_can_be_retrieved(): void
    {
        self::assertSame(1337.42, $this->floatValueType->value());
    }

    #[TestWith([1337.42])]
    public function test_accepts_correct_values(mixed $value): void
    {
        self::assertTrue($this->floatValueType->accepts($value));
    }

    #[TestWith([null])]
    #[TestWith(['Schwifty!'])]
    #[TestWith([404])]
    #[TestWith([404.42])]
    #[TestWith([['foo' => 'bar']])]
    #[TestWith([false])]
    #[TestWith([new stdClass()])]
    public function test_does_not_accept_incorrect_values(mixed $value): void
    {
        self::assertFalse($this->floatValueType->accepts($value));
    }

    public function test_can_cast_float_value(): void
    {
        self::assertTrue($this->floatValueType->canCast(1337.42));
        self::assertTrue($this->floatValueType->canCast('1337.42'));
    }

    public function test_cannot_cast_other_types(): void
    {
        self::assertFalse($this->floatValueType->canCast(404));
        self::assertFalse($this->floatValueType->canCast(42.1337));
        self::assertFalse($this->floatValueType->canCast('42.1337'));
        self::assertFalse($this->floatValueType->canCast(null));
        self::assertFalse($this->floatValueType->canCast(['foo' => 'bar']));
        self::assertFalse($this->floatValueType->canCast('Schwifty!'));
        self::assertFalse($this->floatValueType->canCast(false));
        self::assertFalse($this->floatValueType->canCast(new stdClass()));
    }

    public function test_cast_value_returns_correct_result(): void
    {
        self::assertSame(1337.42, $this->floatValueType->cast('1337.42'));
        self::assertSame(1337.42, $this->floatValueType->cast(1337.42));
    }

    public function test_cast_invalid_value_throws_exception(): void
    {
        $this->expectException(AssertionError::class);

        $this->floatValueType->cast('foo');
    }

    public function test_cast_another_float_value_throws_exception(): void
    {
        $this->expectException(AssertionError::class);

        $this->floatValueType->cast('404.42');
    }

    public function test_string_value_is_correct(): void
    {
        self::assertSame('1337.42', $this->floatValueType->toString());
    }

    public function test_matches_native_float_type(): void
    {
        self::assertTrue($this->floatValueType->matches(new NativeFloatType()));
    }

    public function test_matches_other_float_type_with_same_value(): void
    {
        self::assertTrue($this->floatValueType->matches(new FloatValueType(1337.42)));
    }

    public function test_does_not_match_other_type(): void
    {
        self::assertFalse($this->floatValueType->matches(new FakeType()));
    }

    public function test_matches_mixed_type(): void
    {
        self::assertTrue($this->floatValueType->matches(new MixedType()));
    }

    public function test_matches_union_type_containing_native_float_type(): void
    {
        $unionType = new UnionType(
            new FakeType(),
            new NativeFloatType(),
            new FakeType(),
        );

        self::assertTrue($this->floatValueType->matches($unionType));
    }

    public function test_matches_union_type_containing_float_type_with_same_value(): void
    {
        $unionType = new UnionType(
            new FakeType(),
            new FloatValueType(1337.42),
            new FakeType(),
        );

        self::assertTrue($this->floatValueType->matches($unionType));
    }

    public function test_does_not_match_union_type_not_containing_float_type(): void
    {
        $unionType = new UnionType(new FakeType(), new FakeType());

        self::assertFalse($this->floatValueType->matches($unionType));
    }

    public function test_native_type_is_correct(): void
    {
        self::assertSame('float', (new FloatValueType(1337.42))->nativeType()->toString());
    }
}
