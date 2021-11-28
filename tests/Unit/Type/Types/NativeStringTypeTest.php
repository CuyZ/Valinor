<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Types;

use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Tests\Fixture\Object\StringableObject;
use CuyZ\Valinor\Tests\Traits\TestIsSingleton;
use CuyZ\Valinor\Type\Types\Exception\CannotCastValue;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\NativeStringType;
use CuyZ\Valinor\Type\Types\UnionType;
use PHPUnit\Framework\TestCase;
use stdClass;

final class NativeStringTypeTest extends TestCase
{
    use TestIsSingleton;

    private NativeStringType $stringType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stringType = new NativeStringType();
    }

    public function test_accepts_correct_values(): void
    {
        self::assertTrue($this->stringType->accepts('Schwifty!'));
    }

    public function test_does_not_accept_incorrect_values(): void
    {
        self::assertFalse($this->stringType->accepts(null));
        self::assertFalse($this->stringType->accepts(42.1337));
        self::assertFalse($this->stringType->accepts(404));
        self::assertFalse($this->stringType->accepts(['foo' => 'bar']));
        self::assertFalse($this->stringType->accepts(false));
        self::assertFalse($this->stringType->accepts(new stdClass()));
    }

    public function test_can_cast_stringable_value(): void
    {
        self::assertTrue($this->stringType->canCast('Schwifty!'));
        self::assertTrue($this->stringType->canCast(42.1337));
        self::assertTrue($this->stringType->canCast(404));
        self::assertTrue($this->stringType->canCast(new StringableObject()));
    }

    public function test_cannot_cast_other_types(): void
    {
        self::assertFalse($this->stringType->canCast(null));
        self::assertFalse($this->stringType->canCast(['foo' => 'bar']));
        self::assertFalse($this->stringType->canCast(false));
        self::assertFalse($this->stringType->canCast(new stdClass()));
    }

    /**
     * @dataProvider cast_value_returns_correct_result_data_provider
     *
     * @param mixed $value
     */
    public function test_cast_value_returns_correct_result($value, string $expected): void
    {
        self::assertSame($expected, $this->stringType->cast($value));
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
                'value' => new StringableObject(),
                'expected' => 'foo',
            ],
            'String from string' => [
                'value' => 'bar',
                'expected' => 'bar',
            ],
        ];
    }

    public function test_cast_invalid_value_throws_exception(): void
    {
        $this->expectException(CannotCastValue::class);
        $this->expectExceptionCode(1603216198);
        $this->expectExceptionMessage('Cannot cast from `stdClass` to `string`.');

        $this->stringType->cast(new stdClass());
    }

    public function test_string_value_is_correct(): void
    {
        self::assertSame('string', (string)$this->stringType);
    }

    public function test_matches_same_type(): void
    {
        $stringTypeA = new NativeStringType();
        $stringTypeB = new NativeStringType();

        self::assertTrue($stringTypeA->matches($stringTypeB));
    }

    public function test_does_not_match_other_type(): void
    {
        self::assertFalse($this->stringType->matches(new FakeType()));
    }

    public function test_matches_mixed_type(): void
    {
        self::assertTrue($this->stringType->matches(new MixedType()));
    }

    public function test_matches_union_type_containing_string_type(): void
    {
        $unionType = new UnionType(
            new FakeType(),
            new NativeStringType(),
            new FakeType(),
        );

        self::assertTrue($this->stringType->matches($unionType));
    }

    public function test_does_not_match_union_type_not_containing_string_type(): void
    {
        $unionType = new UnionType(new FakeType(), new FakeType());

        self::assertFalse($this->stringType->matches($unionType));
    }
}
