<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Converter;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Tree\Exception\ConverterHasInvalidCallableParameter;
use CuyZ\Valinor\Mapper\Tree\Exception\ConverterHasNoParameter;
use CuyZ\Valinor\Mapper\Tree\Exception\ConverterHasTooManyParameters;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class ValueConverterMappingTest extends IntegrationTestCase
{
    /**
     * @param array<int, list<callable>> $convertersByPriority
     */
    #[DataProvider('value_is_converted_properly_data_provider')]
    public function test_value_is_converted_properly(string $type, mixed $value, mixed $expectedResult, array $convertersByPriority): void
    {
        try {
            $builder = $this->mapperBuilder();

            foreach ($convertersByPriority as $priority => $converters) {
                foreach ($converters as $converter) {
                    $builder = $builder->registerConverter($converter, $priority);
                }
            }

            $result = $builder
                ->mapper()
                ->map($type, $value);

            self::assertSame($expectedResult, $result);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }
    }

    public static function value_is_converted_properly_data_provider(): iterable
    {
        yield 'string converted to uppercase' => [
            'type' => 'string',
            'value' => 'foo',
            'expectedResult' => 'FOO',
            'convertersByPriority' => [
                [
                    strtoupper(...),
                ],
            ],
        ];

        yield 'string with converter that calls next without value' => [
            'type' => 'string',
            'value' => 'foo',
            'expectedResult' => 'foo!',
            'convertersByPriority' => [
                [
                    fn (string $value, callable $next) => $next() . '!', // @phpstan-ignore binaryOp.invalid (we cannot set closure parameters / see https://github.com/phpstan/phpstan/issues/3770)
                ],
            ],
        ];

        yield 'string with prioritized converters' => [
            'type' => 'string',
            'value' => 'foo',
            'expectedResult' => 'foo?!',
            'convertersByPriority' => [
                10 => [
                    fn (string $value, callable $next): string => $next($value . '!'), // @phpstan-ignore return.type (we cannot set closure parameters / see https://github.com/phpstan/phpstan/issues/3770)
                ],
                50 => [
                    fn (string $value, callable $next): string => $next($value . '?'), // @phpstan-ignore return.type (we cannot set closure parameters / see https://github.com/phpstan/phpstan/issues/3770)
                ],
            ],
        ];

        yield 'string with ignored converters' => [
            'type' => 'string',
            'value' => 'foo',
            'expectedResult' => 'foo!',
            'convertersByPriority' => [
                [
                    fn (int $value, callable $next): string => $next($value) . '?', // @phpstan-ignore binaryOp.invalid (we cannot set closure parameters / see https://github.com/phpstan/phpstan/issues/3770)
                    fn (int $value, callable $next): int => $next($value + 1), // @phpstan-ignore return.type (we cannot set closure parameters / see https://github.com/phpstan/phpstan/issues/3770)
                    fn (string $value, callable $next): string => $next($value . '!'), // @phpstan-ignore return.type (we cannot set closure parameters / see https://github.com/phpstan/phpstan/issues/3770)
                    fn (int $value, callable $next): int => $next($value + 2), // @phpstan-ignore return.type (we cannot set closure parameters / see https://github.com/phpstan/phpstan/issues/3770)
                    fn (int $value, callable $next): string => $next($value) . '#', // @phpstan-ignore binaryOp.invalid (we cannot set closure parameters / see https://github.com/phpstan/phpstan/issues/3770)
                ],
            ],
        ];

        yield 'string with converter that does not call next' => [
            'type' => 'string',
            'value' => 'foo',
            'expectedResult' => 'foo!',
            'convertersByPriority' => [
                [
                    fn (string $value): string => $value . '!',
                    fn (string $value, callable $next): string => $next($value . '?'), // @phpstan-ignore return.type (we cannot set closure parameters / see https://github.com/phpstan/phpstan/issues/3770)
                ],
            ],
        ];
    }

    public function test_converter_with_no_priority_has_priority_0_by_default(): void
    {
        $result = $this->mapperBuilder()
            ->registerConverter(fn (string $value, callable $next) => $next($value . '!'), -1)
            ->registerConverter(fn (string $value, callable $next) => $next($value . '?'))
            ->registerConverter(fn (string $value, callable $next) => $next($value . '#'), 1)
            ->mapper()
            ->map('string', 'foo');

        self::assertSame('foo#?!', $result);
    }

    public function test_converter_is_stopped_if_mapping_error_occurs(): void
    {
        try {
            $this->mapperBuilder()
                ->registerConverter(fn (string $value, callable $next) => $next(42))
                ->mapper()
                ->map('string', 'foo');
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, [
                '*root*' => '[invalid_string] Value 42 is not a valid string.',
            ]);
        }
    }

    public function test_converter_with_no_parameter_throws_exception(): void
    {
        $this->expectException(ConverterHasNoParameter::class);
        $this->expectExceptionCode(1746449489);
        $this->expectExceptionMessageMatches('/The value converter `.*` has no parameter to convert the value to, a typed parameter is required\./');

        $this->mapperBuilder()
            ->registerConverter(fn () => 'bar')
            ->mapper()
            ->map('string', 'foo');
    }

    public function test_converter_with_too_many_parameters_throws_exception(): void
    {
        $this->expectException(ConverterHasTooManyParameters::class);
        $this->expectExceptionCode(1751296711);
        $this->expectExceptionMessageMatches('/Converter must have at most 2 parameters, 3 given for `.*`\./');

        $this->mapperBuilder()
            ->registerConverter(fn (string $foo, callable $next, int $bar) => 'bar')
            ->mapper()
            ->map('string', 'foo');
    }

    public function test_converter_with_invalid_callable_parameter_throws_exception(): void
    {
        $this->expectException(ConverterHasInvalidCallableParameter::class);
        $this->expectExceptionCode(1751296766);
        $this->expectExceptionMessageMatches('/Converter\'s second parameter must be a callable, `int` given for `.*`\./');

        $this->mapperBuilder()
            ->registerConverter(fn (string $foo, int $next) => 'bar')
            ->mapper()
            ->map('string', 'foo');
    }

    public function test_converter_returning_invalid_value_makes_mapping_fail(): void
    {
        try {
            $this->mapperBuilder()
                ->registerConverter(fn (string $value) => '')
                ->mapper()
                ->map('non-empty-string', 'foo');
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, [
                '*root*' => "[invalid_non_empty_string] Value '' is not a valid non-empty string.",
            ]);
        }
    }
}
