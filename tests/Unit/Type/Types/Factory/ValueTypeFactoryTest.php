<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Types\Factory;

use CuyZ\Valinor\Tests\Fixture\Enum\PureEnum;
use CuyZ\Valinor\Tests\Unit\UnitTestCase;
use CuyZ\Valinor\Type\Types\Factory\CannotBuildTypeFromValue;
use CuyZ\Valinor\Type\Types\Factory\ValueTypeFactory;
use DateTime;
use PHPUnit\Framework\Attributes\DataProvider;
use stdClass;

final class ValueTypeFactoryTest extends UnitTestCase
{
    #[DataProvider('type_from_value_returns_expected_type_data_provider')]
    public function test_type_from_value_returns_expected_type(mixed $value, string $expectedType): void
    {
        $type = ValueTypeFactory::from($value);

        self::assertSame($expectedType, $type->toString());
    }

    public static function type_from_value_returns_expected_type_data_provider(): iterable
    {
        yield 'true' => [
            'value' => true,
            'expectedType' => 'true',
        ];
        yield 'false' => [
            'value' => false,
            'expectedType' => 'false',
        ];
        yield 'float' => [
            'value' => 1337.42,
            'expectedType' => '1337.42',
        ];
        yield 'integer' => [
            'value' => 1337,
            'expectedType' => '1337',
        ];
        yield 'string value' => [
            'value' => 'foo bar',
            'expectedType' => "'foo bar'",
        ];
        yield 'string value with class name' => [
            'value' => stdClass::class,
            'expectedType' => 'class-string<stdClass>',
        ];
        yield 'string value with single quote' => [
            'value' => "What's up",
            'expectedType' => '"What\'s up"',
        ];
        yield 'string value with double quote' => [
            'value' => 'This is "some" test',
            'expectedType' => "'This is \"some\" test'",
        ];
        yield 'string value with both quote' => [
            'value' => 'This \'is\' "some" test',
            'expectedType' => "'This \'is\' \"some\" test'",
        ];
        yield 'array of scalar' => [
            'value' => ['foo' => 'bar', 'baz' => 'fiz'],
            'expectedType' => "array{foo: 'bar', baz: 'fiz'}",
        ];
        yield 'nested array of scalar' => [
            'value' => ['foo' => ['foo' => 'bar', 'baz' => 'fiz']],
            'expectedType' => "array{foo: array{foo: 'bar', baz: 'fiz'}}",
        ];

        yield 'enum' => [
            'value' => PureEnum::FOO,
            'expectedType' => PureEnum::class . '::FOO',
        ];
    }

    public function test_invalid_value_throws_exception(): void
    {
        $this->expectException(CannotBuildTypeFromValue::class);
        $this->expectExceptionMessage('Cannot build type from value of type `object`.');

        ValueTypeFactory::from(new DateTime());
    }
}
