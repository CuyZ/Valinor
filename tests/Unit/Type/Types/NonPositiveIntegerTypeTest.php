<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Types;

use AssertionError;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Tests\Traits\TestIsSingleton;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\NativeIntegerType;
use CuyZ\Valinor\Type\Types\NonPositiveIntegerType;
use CuyZ\Valinor\Type\Types\PositiveIntegerType;
use CuyZ\Valinor\Type\Types\UnionType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use stdClass;

final class NonPositiveIntegerTypeTest extends TestCase
{
    use TestIsSingleton;

    private NonPositiveIntegerType $nonPositiveIntegerType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->nonPositiveIntegerType = new NonPositiveIntegerType();
    }

    #[TestWith([0])]
    #[TestWith([-404])]
    public function test_accepts_correct_values(mixed $value): void
    {
        self::assertTrue($this->nonPositiveIntegerType->accepts($value));
    }

    #[TestWith([null])]
    #[TestWith(['Schwifty!'])]
    #[TestWith([404])]
    #[TestWith([42.1337])]
    #[TestWith([['foo' => 'bar']])]
    #[TestWith([false])]
    #[TestWith([new stdClass()])]
    public function test_does_not_accept_incorrect_values(mixed $value): void
    {
        self::assertFalse($this->nonPositiveIntegerType->accepts($value));
    }

    public function test_can_cast_integer_value(): void
    {
        self::assertTrue($this->nonPositiveIntegerType->canCast(0));
        self::assertTrue($this->nonPositiveIntegerType->canCast(-404));
        self::assertTrue($this->nonPositiveIntegerType->canCast('-404'));
        self::assertTrue($this->nonPositiveIntegerType->canCast(-404.00));
    }

    public function test_cannot_cast_other_types(): void
    {
        self::assertFalse($this->nonPositiveIntegerType->canCast(null));
        self::assertFalse($this->nonPositiveIntegerType->canCast(42));
        self::assertFalse($this->nonPositiveIntegerType->canCast(-42.1337));
        self::assertFalse($this->nonPositiveIntegerType->canCast(42.1337));
        self::assertFalse($this->nonPositiveIntegerType->canCast(['foo' => 'bar']));
        self::assertFalse($this->nonPositiveIntegerType->canCast('Schwifty!'));
        self::assertFalse($this->nonPositiveIntegerType->canCast(false));
        self::assertFalse($this->nonPositiveIntegerType->canCast(new stdClass()));
    }

    #[DataProvider('cast_value_returns_correct_result_data_provider')]
    public function test_cast_value_returns_correct_result(mixed $value, int $expected): void
    {
        self::assertSame($expected, $this->nonPositiveIntegerType->cast($value));
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
            'Zero from string' => [
                'value' => '0',
                'expected' => 0,
            ],
        ];
    }

    public function test_cast_invalid_value_throws_exception(): void
    {
        $this->expectException(AssertionError::class);

        $this->nonPositiveIntegerType->cast('foo');
    }

    public function test_cast_invalid_positive_value_throws_exception(): void
    {
        $this->expectException(AssertionError::class);

        $this->nonPositiveIntegerType->cast(1337);
    }

    public function test_string_value_is_correct(): void
    {
        self::assertSame('non-positive-int', $this->nonPositiveIntegerType->toString());
    }

    public function test_matches_valid_integer_type(): void
    {
        self::assertTrue($this->nonPositiveIntegerType->matches(new NativeIntegerType()));
        self::assertTrue($this->nonPositiveIntegerType->matches($this->nonPositiveIntegerType));
        self::assertFalse($this->nonPositiveIntegerType->matches(new PositiveIntegerType()));
    }

    public function test_does_not_match_other_type(): void
    {
        self::assertFalse($this->nonPositiveIntegerType->matches(new FakeType()));
    }

    public function test_matches_mixed_type(): void
    {
        self::assertTrue($this->nonPositiveIntegerType->matches(new MixedType()));
    }

    public function test_matches_union_type_containing_integer_type(): void
    {
        $union = new UnionType(new FakeType(), new NativeIntegerType(), new FakeType());
        $unionWithSelf = new UnionType(new FakeType(), new NonPositiveIntegerType(), new FakeType());

        self::assertTrue($this->nonPositiveIntegerType->matches($union));
        self::assertTrue($this->nonPositiveIntegerType->matches($unionWithSelf));
    }

    public function test_does_not_match_union_type_not_containing_integer_type(): void
    {
        $unionType = new UnionType(new FakeType(), new FakeType());

        self::assertFalse($this->nonPositiveIntegerType->matches($unionType));
    }

    public function test_native_type_is_correct(): void
    {
        self::assertSame('int', (new NonPositiveIntegerType())->nativeType()->toString());
    }
}
