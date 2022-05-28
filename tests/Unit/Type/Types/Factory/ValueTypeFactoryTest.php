<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Types\Factory;

use CuyZ\Valinor\Tests\Fixture\Enum\PureEnum;
use CuyZ\Valinor\Type\Types\Factory\CannotBuildTypeFromValue;
use CuyZ\Valinor\Type\Types\Factory\ValueTypeFactory;
use DateTime;
use PHPUnit\Framework\TestCase;

final class ValueTypeFactoryTest extends TestCase
{
    /**
     * @dataProvider type_from_value_returns_expected_type_data_provider
     *
     * @param mixed $value
     */
    public function test_type_from_value_returns_expected_type($value, string $expectedType): void
    {
        $type = ValueTypeFactory::from($value);

        self::assertSame($expectedType, $type->toString());
    }

    public function type_from_value_returns_expected_type_data_provider(): iterable
    {
        yield 'true' => [
            'value' => true,
            'type' => 'true',
        ];
        yield 'false' => [
            'value' => false,
            'type' => 'false',
        ];
        yield 'float' => [
            'value' => 1337.42,
            'type' => '1337.42',
        ];
        yield 'integer' => [
            'value' => 1337,
            'type' => '1337',
        ];
        yield 'string value' => [
            'value' => 'foo bar',
            'type' => "'foo bar'",
        ];
        yield 'string value with single quote' => [
            'value' => "What's up",
            'type' => '"What\'s up"',
        ];
        yield 'string value with double quote' => [
            'value' => 'This is "some" test',
            'type' => "'This is \"some\" test'",
        ];
        yield 'string value with both quote' => [
            'value' => 'This \'is\' "some" test',
            'type' => "'This \'is\' \"some\" test'",
        ];
        yield 'array of scalar' => [
            'value' => ['foo' => 'bar', 'baz' => 'fiz'],
            'type' => "array{foo: 'bar', baz: 'fiz'}",
        ];
        yield 'nested array of scalar' => [
            'value' => ['foo' => ['foo' => 'bar', 'baz' => 'fiz']],
            'type' => "array{foo: array{foo: 'bar', baz: 'fiz'}}",
        ];

        if (PHP_VERSION_ID >= 8_01_00) {
            yield 'enum' => [
                'symbol' => PureEnum::FOO,
                'token' => PureEnum::class . '::FOO',
            ];
        }
    }

    public function test_invalid_value_throws_exception(): void
    {
        $this->expectException(CannotBuildTypeFromValue::class);
        $this->expectExceptionCode(1653592997);
        $this->expectExceptionMessage('Cannot build type from value of type `object`.');

        ValueTypeFactory::from(new DateTime());
    }
}
