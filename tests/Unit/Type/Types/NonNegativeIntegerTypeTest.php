<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Types;

use AssertionError;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Tests\Traits\TestIsSingleton;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\NativeIntegerType;
use CuyZ\Valinor\Type\Types\NonNegativeIntegerType;
use CuyZ\Valinor\Type\Types\PositiveIntegerType;
use CuyZ\Valinor\Type\Types\UnionType;
use PHPUnit\Framework\TestCase;
use stdClass;

final class NonNegativeIntegerTypeTest extends TestCase
{
    use TestIsSingleton;

    private NonNegativeIntegerType $nonNegativeIntegerType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->nonNegativeIntegerType = new NonNegativeIntegerType();
    }

    public function test_accepts_correct_values(): void
    {
        self::assertTrue($this->nonNegativeIntegerType->accepts(0));
        self::assertTrue($this->nonNegativeIntegerType->accepts(404));
    }

    public function test_does_not_accept_incorrect_values(): void
    {
        self::assertFalse($this->nonNegativeIntegerType->accepts(null));
        self::assertFalse($this->nonNegativeIntegerType->accepts('Schwifty!'));
        self::assertFalse($this->nonNegativeIntegerType->accepts(-404));
        self::assertFalse($this->nonNegativeIntegerType->accepts(42.1337));
        self::assertFalse($this->nonNegativeIntegerType->accepts(['foo' => 'bar']));
        self::assertFalse($this->nonNegativeIntegerType->accepts(false));
        self::assertFalse($this->nonNegativeIntegerType->accepts(new stdClass()));
    }

    public function test_can_cast_integer_value(): void
    {
        self::assertTrue($this->nonNegativeIntegerType->canCast(0));
        self::assertTrue($this->nonNegativeIntegerType->canCast(404));
        self::assertTrue($this->nonNegativeIntegerType->canCast('404'));
        self::assertTrue($this->nonNegativeIntegerType->canCast(404.00));
    }

    public function test_cannot_cast_other_types(): void
    {
        self::assertFalse($this->nonNegativeIntegerType->canCast(null));
        self::assertFalse($this->nonNegativeIntegerType->canCast(-42));
        self::assertFalse($this->nonNegativeIntegerType->canCast(-42.1337));
        self::assertFalse($this->nonNegativeIntegerType->canCast(42.1337));
        self::assertFalse($this->nonNegativeIntegerType->canCast(['foo' => 'bar']));
        self::assertFalse($this->nonNegativeIntegerType->canCast('Schwifty!'));
        self::assertFalse($this->nonNegativeIntegerType->canCast(false));
        self::assertFalse($this->nonNegativeIntegerType->canCast(new stdClass()));
    }

    /**
     * @dataProvider cast_value_returns_correct_result_data_provider
     */
    public function test_cast_value_returns_correct_result(mixed $value, int $expected): void
    {
        self::assertSame($expected, $this->nonNegativeIntegerType->cast($value));
    }

    public function cast_value_returns_correct_result_data_provider(): array
    {
        return [
            'Integer from float' => [
                'value' => 404.00,
                'expected' => 404,
            ],
            'Integer from string' => [
                'value' => '42',
                'expected' => 42,
            ],
            'Integer from integer' => [
                'value' => 1337,
                'expected' => 1337,
            ],
            'Zero from string' => [
                'value' => '0',
                'expected' => 0,
            ],
        ];
    }

    public function test_cast_invalid_value_throws_exception(): void
    {
        $this->expectException(AssertionError::class);

        $this->nonNegativeIntegerType->cast('foo');
    }

    public function test_cast_invalid_positive_value_throws_exception(): void
    {
        $this->expectException(AssertionError::class);

        $this->nonNegativeIntegerType->cast(-1337);
    }

    public function test_string_value_is_correct(): void
    {
        self::assertSame('non-negative-int', $this->nonNegativeIntegerType->toString());
    }

    public function test_matches_valid_integer_type(): void
    {
        self::assertTrue($this->nonNegativeIntegerType->matches(new NativeIntegerType()));
        self::assertTrue($this->nonNegativeIntegerType->matches($this->nonNegativeIntegerType));
        self::assertFalse($this->nonNegativeIntegerType->matches(new PositiveIntegerType()));
    }

    public function test_does_not_match_other_type(): void
    {
        self::assertFalse($this->nonNegativeIntegerType->matches(new FakeType()));
    }

    public function test_matches_mixed_type(): void
    {
        self::assertTrue($this->nonNegativeIntegerType->matches(new MixedType()));
    }

    public function test_matches_union_type_containing_integer_type(): void
    {
        $union = new UnionType(new FakeType(), new NativeIntegerType(), new FakeType());
        $unionWithSelf = new UnionType(new FakeType(), new NonNegativeIntegerType(), new FakeType());

        self::assertTrue($this->nonNegativeIntegerType->matches($union));
        self::assertTrue($this->nonNegativeIntegerType->matches($unionWithSelf));
    }

    public function test_does_not_match_union_type_not_containing_integer_type(): void
    {
        $unionType = new UnionType(new FakeType(), new FakeType());

        self::assertFalse($this->nonNegativeIntegerType->matches($unionType));
    }
}
