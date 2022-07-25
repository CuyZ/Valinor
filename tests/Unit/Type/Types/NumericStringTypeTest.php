<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Types;

use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Tests\Fixture\Object\StringableObject;
use CuyZ\Valinor\Tests\Traits\TestIsSingleton;
use CuyZ\Valinor\Type\Types\Exception\CannotCastValue;
use CuyZ\Valinor\Type\Types\Exception\InvalidNumericStringValue;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\NativeStringType;
use CuyZ\Valinor\Type\Types\NonEmptyStringType;
use CuyZ\Valinor\Type\Types\NumericStringType;
use CuyZ\Valinor\Type\Types\UnionType;
use PHPUnit\Framework\TestCase;
use stdClass;

final class NumericStringTypeTest extends TestCase
{
    use TestIsSingleton;

    private NumericStringType $numericStringType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->numericStringType = new NumericStringType();
    }

    public function test_accepts_correct_values(): void
    {
        self::assertTrue($this->numericStringType->accepts('777.7'));
        self::assertTrue($this->numericStringType->accepts('0'));
    }

    public function test_does_not_accept_incorrect_values(): void
    {
        self::assertFalse($this->numericStringType->accepts(null));
        self::assertFalse($this->numericStringType->accepts(''));
        self::assertFalse($this->numericStringType->accepts(42.1337));
        self::assertFalse($this->numericStringType->accepts(404));
        self::assertFalse($this->numericStringType->accepts(['foo' => 'bar']));
        self::assertFalse($this->numericStringType->accepts(false));
        self::assertFalse($this->numericStringType->accepts(new stdClass()));
    }

    public function test_can_cast_stringable_value(): void
    {
        self::assertTrue($this->numericStringType->canCast('Schwifty!'));
        self::assertTrue($this->numericStringType->canCast(42.1337));
        self::assertTrue($this->numericStringType->canCast(404));
        self::assertTrue($this->numericStringType->canCast(new StringableObject()));
    }

    public function test_cannot_cast_other_types(): void
    {
        self::assertFalse($this->numericStringType->canCast(null));
        self::assertFalse($this->numericStringType->canCast(['foo' => 'bar']));
        self::assertFalse($this->numericStringType->canCast(false));
        self::assertFalse($this->numericStringType->canCast(new stdClass()));
    }

    /**
     * @dataProvider cast_value_returns_correct_result_data_provider
     *
     * @param mixed $value
     */
    public function test_cast_value_returns_correct_result($value, string $expected): void
    {
        self::assertSame($expected, $this->numericStringType->cast($value));
    }

    public function cast_value_returns_correct_result_data_provider(): array
    {
        return [
            'String from float' => [
                'value' => 404.42,
                'expected' => '404.42',
            ],
            'String from integer' => [
                'value' => 42,
                'expected' => '42',
            ],
            'String from object' => [
                'value' => new StringableObject('700'),
                'expected' => '700',
            ],
        ];
    }

    public function test_cast_invalid_value_throws_exception(): void
    {
        $this->expectException(CannotCastValue::class);
        $this->expectExceptionCode(1603216198);
        $this->expectExceptionMessage('Cannot cast object(stdClass) to `numeric-string`.');

        $this->numericStringType->cast(new stdClass());
    }

    public function test_cast_invalid_numeric_throws_exception(): void
    {
        $this->expectException(InvalidNumericStringValue::class);
        $this->expectExceptionCode(1632923705);
        $this->expectExceptionMessage('Invalid value qqq: it must be a numeric.');

        $this->numericStringType->cast('qqq');
    }

    public function test_string_value_is_correct(): void
    {
        self::assertSame('numeric-string', $this->numericStringType->toString());
    }

    public function test_matches_same_type(): void
    {
        $numericStringTypeA = new NumericStringType();
        $numericStringTypeB = new NumericStringType();

        self::assertTrue($numericStringTypeA->matches($numericStringTypeB));
    }

    public function test_matches_native_string_type(): void
    {
        self::assertTrue($this->numericStringType->matches(new NativeStringType()));
    }

    public function test_matches_non_empty_string_type(): void
    {
        self::assertTrue($this->numericStringType->matches(new NonEmptyStringType()));
    }

    public function test_does_not_match_other_type(): void
    {
        self::assertFalse($this->numericStringType->matches(new FakeType()));
    }

    public function test_matches_mixed_type(): void
    {
        self::assertTrue($this->numericStringType->matches(new MixedType()));
    }

    public function test_matches_union_type_containing_string_type(): void
    {
        $unionType = new UnionType(
            new FakeType(),
            new NumericStringType(),
            new FakeType(),
        );

        self::assertTrue($this->numericStringType->matches($unionType));
    }

    public function test_does_not_match_union_type_not_containing_string_type(): void
    {
        $unionType = new UnionType(new FakeType(), new FakeType());

        self::assertFalse($this->numericStringType->matches($unionType));
    }
}
