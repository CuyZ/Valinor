<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Types;

use AssertionError;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Tests\Traits\TestIsSingleton;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\NativeIntegerType;
use CuyZ\Valinor\Type\Types\NegativeIntegerType;
use CuyZ\Valinor\Type\Types\PositiveIntegerType;
use CuyZ\Valinor\Type\Types\UnionType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

final class NegativeIntegerTypeTest extends TestCase
{
    use TestIsSingleton;

    private NegativeIntegerType $negativeIntegerType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->negativeIntegerType = new NegativeIntegerType();
    }

    public function test_accepts_correct_values(): void
    {
        self::assertTrue($this->negativeIntegerType->accepts(-404));
    }

    public function test_does_not_accept_incorrect_values(): void
    {
        self::assertFalse($this->negativeIntegerType->accepts(null));
        self::assertFalse($this->negativeIntegerType->accepts('Schwifty!'));
        self::assertFalse($this->negativeIntegerType->accepts(0));
        self::assertFalse($this->negativeIntegerType->accepts(404));
        self::assertFalse($this->negativeIntegerType->accepts(42.1337));
        self::assertFalse($this->negativeIntegerType->accepts(['foo' => 'bar']));
        self::assertFalse($this->negativeIntegerType->accepts(false));
        self::assertFalse($this->negativeIntegerType->accepts(new stdClass()));
    }

    public function test_can_cast_integer_value(): void
    {
        self::assertTrue($this->negativeIntegerType->canCast(-404));
        self::assertTrue($this->negativeIntegerType->canCast('-404'));
        self::assertTrue($this->negativeIntegerType->canCast(-404.00));
    }

    public function test_cannot_cast_other_types(): void
    {
        self::assertFalse($this->negativeIntegerType->canCast(null));
        self::assertFalse($this->negativeIntegerType->canCast(-42.1337));
        self::assertFalse($this->negativeIntegerType->canCast(42.1337));
        self::assertFalse($this->negativeIntegerType->canCast(['foo' => 'bar']));
        self::assertFalse($this->negativeIntegerType->canCast('Schwifty!'));
        self::assertFalse($this->negativeIntegerType->canCast(false));
        self::assertFalse($this->negativeIntegerType->canCast(new stdClass()));
    }

    #[DataProvider('cast_value_returns_correct_result_data_provider')]
    public function test_cast_value_returns_correct_result(mixed $value, int $expected): void
    {
        self::assertSame($expected, $this->negativeIntegerType->cast($value));
    }

    public static function cast_value_returns_correct_result_data_provider(): array
    {
        return [
            'Integer from float' => [
                'value' => -404.00,
                'expected' => -404,
            ],
            'Integer from string' => [
                'value' => '-42',
                'expected' => -42,
            ],
            'Integer from integer' => [
                'value' => -1337,
                'expected' => -1337,
            ],
        ];
    }

    public function test_cast_invalid_value_throws_exception(): void
    {
        $this->expectException(AssertionError::class);

        $this->negativeIntegerType->cast('foo');
    }

    public function test_cast_invalid_positive_value_throws_exception(): void
    {
        $this->expectException(AssertionError::class);

        $this->negativeIntegerType->cast(1337);
    }

    public function test_cast_positive_value_with_zero_throws_exception(): void
    {
        $this->expectException(AssertionError::class);

        $this->negativeIntegerType->cast(0);
    }

    public function test_string_value_is_correct(): void
    {
        self::assertSame('negative-int', $this->negativeIntegerType->toString());
    }

    public function test_matches_valid_integer_type(): void
    {
        self::assertTrue($this->negativeIntegerType->matches(new NativeIntegerType()));
        self::assertTrue($this->negativeIntegerType->matches($this->negativeIntegerType));
        self::assertFalse($this->negativeIntegerType->matches(new PositiveIntegerType()));
    }

    public function test_does_not_match_other_type(): void
    {
        self::assertFalse($this->negativeIntegerType->matches(new FakeType()));
    }

    public function test_matches_mixed_type(): void
    {
        self::assertTrue($this->negativeIntegerType->matches(new MixedType()));
    }

    public function test_matches_union_type_containing_integer_type(): void
    {
        $union = new UnionType(new FakeType(), new NativeIntegerType(), new FakeType());
        $unionWithSelf = new UnionType(new FakeType(), new NegativeIntegerType(), new FakeType());

        self::assertTrue($this->negativeIntegerType->matches($union));
        self::assertTrue($this->negativeIntegerType->matches($unionWithSelf));
    }

    public function test_does_not_match_union_type_not_containing_integer_type(): void
    {
        $unionType = new UnionType(new FakeType(), new FakeType());

        self::assertFalse($this->negativeIntegerType->matches($unionType));
    }
}
