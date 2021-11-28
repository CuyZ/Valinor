<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Types;

use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Tests\Traits\TestIsSingleton;
use CuyZ\Valinor\Type\Types\BooleanType;
use CuyZ\Valinor\Type\Types\Exception\CannotCastValue;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\UnionType;
use PHPUnit\Framework\TestCase;
use stdClass;

final class BooleanTypeTest extends TestCase
{
    use TestIsSingleton;

    private BooleanType $booleanType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->booleanType = new BooleanType();
    }

    public function test_accepts_correct_values(): void
    {
        self::assertTrue($this->booleanType->accepts(true));
        self::assertTrue($this->booleanType->accepts(false));
    }

    public function test_does_not_accept_incorrect_values(): void
    {
        self::assertFalse($this->booleanType->accepts(null));
        self::assertFalse($this->booleanType->accepts('Schwifty!'));
        self::assertFalse($this->booleanType->accepts(42.1337));
        self::assertFalse($this->booleanType->accepts(404));
        self::assertFalse($this->booleanType->accepts(['foo' => 'bar']));
        self::assertFalse($this->booleanType->accepts(new stdClass()));
    }

    public function test_can_cast_boolean_value(): void
    {
        self::assertTrue($this->booleanType->canCast(false));
    }

    public function test_cannot_cast_other_types(): void
    {
        self::assertFalse($this->booleanType->canCast(null));
        self::assertFalse($this->booleanType->canCast(42.1337));
        self::assertFalse($this->booleanType->canCast(404));
        self::assertFalse($this->booleanType->canCast('Schwifty!'));
        self::assertFalse($this->booleanType->canCast(['foo' => 'bar']));
        self::assertFalse($this->booleanType->canCast(new stdClass()));
    }

    /**
     * @dataProvider cast_value_returns_correct_result_data_provider
     *
     * @param mixed $value
     */
    public function test_cast_value_returns_correct_result($value, bool $expected): void
    {
        $result = $this->booleanType->cast($value);

        self::assertSame($expected, $result);
    }

    public function cast_value_returns_correct_result_data_provider(): array
    {
        return [
            'True from integer-string' => [
                'value' => '1',
                'expected' => true,
            ],
            'False from integer-string' => [
                'value' => '0',
                'expected' => false,
            ],
            'True from integer' => [
                'value' => 1,
                'expected' => true,
            ],
            'False from integer' => [
                'value' => 0,
                'expected' => false,
            ],
            'True from string' => [
                'value' => 'true',
                'expected' => true,
            ],
            'False from string' => [
                'value' => 'false',
                'expected' => false,
            ],
            'True from boolean' => [
                'value' => true,
                'expected' => true,
            ],
            'False from boolean' => [
                'value' => false,
                'expected' => false,
            ],
        ];
    }

    public function test_cast_invalid_value_throws_exception(): void
    {
        $this->expectException(CannotCastValue::class);
        $this->expectExceptionCode(1603216198);
        $this->expectExceptionMessage('Cannot cast from `string` to `bool`.');

        $this->booleanType->cast('foo');
    }

    public function test_string_value_is_correct(): void
    {
        self::assertSame('bool', (string)$this->booleanType);
    }

    public function test_matches_same_type(): void
    {
        self::assertTrue((new BooleanType())->matches(new BooleanType()));
    }

    public function test_does_not_match_other_type(): void
    {
        self::assertFalse($this->booleanType->matches(new FakeType()));
    }

    public function test_matches_mixed_type(): void
    {
        self::assertTrue($this->booleanType->matches(new MixedType()));
    }

    public function test_matches_union_type_containing_boolean_type(): void
    {
        $unionType = new UnionType(
            new FakeType(),
            new BooleanType(),
            new FakeType(),
        );

        self::assertTrue($this->booleanType->matches($unionType));
    }

    public function test_does_not_match_union_type_not_containing_boolean_type(): void
    {
        $unionType = new UnionType(new FakeType(), new FakeType());

        self::assertFalse($this->booleanType->matches($unionType));
    }
}
