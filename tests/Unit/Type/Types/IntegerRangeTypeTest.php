<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Types;

use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Type\Parser\Exception\Scalar\ReversedValuesForIntegerRange;
use CuyZ\Valinor\Type\Parser\Exception\Scalar\SameValueForIntegerRange;
use CuyZ\Valinor\Type\Types\Exception\CannotCastValue;
use CuyZ\Valinor\Type\Types\Exception\InvalidIntegerRangeValue;
use CuyZ\Valinor\Type\Types\IntegerRangeType;
use CuyZ\Valinor\Type\Types\IntegerValueType;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\NegativeIntegerType;
use CuyZ\Valinor\Type\Types\PositiveIntegerType;
use CuyZ\Valinor\Type\Types\UnionType;
use PHPUnit\Framework\TestCase;
use stdClass;

final class IntegerRangeTypeTest extends TestCase
{
    private IntegerRangeType $type;

    protected function setUp(): void
    {
        parent::setUp();

        $this->type = new IntegerRangeType(-42, 42);
    }

    public function test_range_with_same_min_and_max_throws_exception(): void
    {
        $this->expectException(SameValueForIntegerRange::class);
        $this->expectExceptionCode(1638786927);
        $this->expectExceptionMessage('The min and max values for integer range must be different, `42` was given.');

        new IntegerRangeType(42, 42);
    }

    public function test_range_with_same_min_greater_than_max_throws_exception(): void
    {
        $this->expectException(ReversedValuesForIntegerRange::class);
        $this->expectExceptionCode(1638787061);
        $this->expectExceptionMessage('The min value must be less than the max for integer range `int<1337, 42>`.');

        new IntegerRangeType(1337, 42);
    }

    public function test_accepts_correct_values(): void
    {
        self::assertTrue($this->type->accepts(-42));
        self::assertTrue($this->type->accepts(0));
        self::assertTrue($this->type->accepts(42));
    }

    public function test_does_not_accept_incorrect_values(): void
    {
        self::assertFalse($this->type->accepts(-1337));
        self::assertFalse($this->type->accepts(1337));
        self::assertFalse($this->type->accepts(null));
        self::assertFalse($this->type->accepts('Schwifty!'));
        self::assertFalse($this->type->accepts(42.1337));
        self::assertFalse($this->type->accepts(['foo' => 'bar']));
        self::assertFalse($this->type->accepts(false));
        self::assertFalse($this->type->accepts(new stdClass()));
    }

    public function test_can_cast_integer_value(): void
    {
        self::assertTrue($this->type->canCast(42));
        self::assertTrue($this->type->canCast('42'));
        self::assertTrue($this->type->canCast(42.00));
    }

    public function test_cannot_cast_other_types(): void
    {
        self::assertFalse($this->type->canCast(null));
        self::assertFalse($this->type->canCast(42.1337));
        self::assertFalse($this->type->canCast(['foo' => 'bar']));
        self::assertFalse($this->type->canCast('Schwifty!'));
        self::assertFalse($this->type->canCast(false));
        self::assertFalse($this->type->canCast(new stdClass()));
    }

    /**
     * @dataProvider cast_value_returns_correct_result_data_provider
     *
     * @param mixed $value
     */
    public function test_cast_value_returns_correct_result($value, int $expected): void
    {
        self::assertSame($expected, $this->type->cast($value));
    }

    public function cast_value_returns_correct_result_data_provider(): array
    {
        return [
            'Integer from float' => [
                'value' => 42.00,
                'expected' => 42,
            ],
            'Integer from string' => [
                'value' => '42',
                'expected' => 42,
            ],
            'Integer from integer' => [
                'value' => 42,
                'expected' => 42,
            ],
        ];
    }

    public function test_cast_invalid_value_throws_exception(): void
    {
        $this->expectException(CannotCastValue::class);
        $this->expectExceptionCode(1603216198);
        $this->expectExceptionMessage("Cannot cast 'foo' to `{$this->type->toString()}`.");

        $this->type->cast('foo');
    }

    public function test_cast_invalid_integer_value_throws_exception(): void
    {
        $this->expectException(InvalidIntegerRangeValue::class);
        $this->expectExceptionCode(1638785150);
        $this->expectExceptionMessage("Invalid value 1337: it must be an integer between {$this->type->min()} and {$this->type->max()}.");

        $this->type->cast(1337);
    }

    public function test_string_value_is_correct(): void
    {
        self::assertSame('int<42, 1337>', (new IntegerRangeType(42, 1337))->toString());
        self::assertSame('int<-1337, -42>', (new IntegerRangeType(-1337, -42))->toString());
        self::assertSame('int<min, max>', (new IntegerRangeType(PHP_INT_MIN, PHP_INT_MAX))->toString());
    }

    public function test_matches_same_type_with_same_range(): void
    {
        self::assertTrue((new IntegerRangeType(-42, 42))->matches(new IntegerRangeType(-42, 42)));
    }

    public function test_does_not_match_same_type_with_different_range(): void
    {
        self::assertFalse((new IntegerRangeType(-42, 42))->matches(new IntegerRangeType(-1337, 42)));
    }

    public function test_matches_integer_value_when_value_is_in_range(): void
    {
        self::assertTrue((new IntegerRangeType(42, 1337))->matches(new IntegerValueType(42)));
    }

    public function test_does_not_match_integer_value_when_value_is_not_in_range(): void
    {
        self::assertFalse((new IntegerRangeType(42, 1337))->matches(new IntegerValueType(-1337)));
    }

    public function test_matches_positive_integer_when_range_is_positive(): void
    {
        self::assertTrue((new IntegerRangeType(42, 1337))->matches(new PositiveIntegerType()));
    }

    public function test_does_not_match_positive_integer_when_min_is_negative(): void
    {
        self::assertFalse((new IntegerRangeType(-42, 1337))->matches(new PositiveIntegerType()));
    }

    public function test_does_not_match_positive_integer_when_max_is_negative(): void
    {
        self::assertFalse((new IntegerRangeType(-1337, -42))->matches(new PositiveIntegerType()));
    }

    public function test_matches_negative_integer_when_range_is_negative(): void
    {
        self::assertTrue((new IntegerRangeType(-1337, -42))->matches(new NegativeIntegerType()));
    }

    public function test_does_not_match_negative_integer_when_min_is_positive(): void
    {
        self::assertFalse((new IntegerRangeType(42, 1337))->matches(new NegativeIntegerType()));
    }

    public function test_does_not_match_negative_integer_when_max_is_positive(): void
    {
        self::assertFalse((new IntegerRangeType(-42, 1337))->matches(new NegativeIntegerType()));
    }

    public function test_does_not_match_other_type(): void
    {
        self::assertFalse($this->type->matches(new FakeType()));
    }

    public function test_matches_mixed_type(): void
    {
        self::assertTrue($this->type->matches(new MixedType()));
    }

    public function test_matches_union_type_containing_integer_range_type(): void
    {
        $union = new UnionType(new FakeType(), new IntegerRangeType(-42, 42), new FakeType());

        self::assertTrue($this->type->matches($union));
    }

    public function test_does_not_match_union_type_not_containing_integer_range_type(): void
    {
        $unionType = new UnionType(new FakeType(), new FakeType());

        self::assertFalse($this->type->matches($unionType));
    }
}
