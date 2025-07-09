<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Tree\Exception\ValueConverterHasNoArgument;
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

    public function test_converter_with_no_argument_throws_exception(): void
    {
        $this->expectException(ValueConverterHasNoArgument::class);
        $this->expectExceptionCode(1746449489);
        $this->expectExceptionMessageMatches('/The value converter `.*` has no argument to convert the value to, a typed argument is required\./');

        $this->mapperBuilder()
            ->registerConverter(fn () => 'bar')
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

    public function test_can_convert_snake_case_to_camel_case_for_object(): void
    {
        $class = new class () {
            public string $someValue;

            /** @var array<string> */
            public array $someOtherValue;
        };

        try {
            $result = $this->mapperBuilder()
                // @phpstan-ignore return.type (we cannot set closure parameters / see https://github.com/phpstan/phpstan/issues/3770)
                ->registerConverter(fn (array $values, callable $next): object => $next(array_combine(
                    array_map(
                        fn ($key) => lcfirst(str_replace([' ', '_', '-'], '', ucwords($key, ' _-'))),
                        array_keys($values),
                    ),
                    $values,
                )))
                ->mapper()
                ->map($class::class, [
                    'some_value' => 'foo',
                    'some_other_value' => [
                        'bar' => 'bar',
                        'baz' => 'baz',
                    ],
                ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $result->someValue);
        self::assertSame(['bar' => 'bar', 'baz' => 'baz'], $result->someOtherValue);
    }

    public function test_can_rename_keys_for_object(): void
    {
        $class = new class () {
            public string $someValue;

            public int $someOtherValue;
        };

        $renameKeys = fn (array $values, array $keyReplacements) => array_combine(
            // @phpstan-ignore argument.type (we cannot set closure parameters / see https://github.com/phpstan/phpstan/issues/3770)
            array_map(
                fn ($key) => $keyReplacements[$key] ?? $key,
                array_keys($values),
            ),
            $values,
        );

        try {
            $result = $this->mapperBuilder()
                // @phpstan-ignore return.type (we cannot set closure parameters / see https://github.com/phpstan/phpstan/issues/3770)
                ->registerConverter(fn (array $values, callable $next): object => $next($renameKeys($values, [
                    'aValue' => 'someValue',
                    'anotherValue' => 'someOtherValue',
                ])))
                ->mapper()
                ->map($class::class, [
                    'aValue' => 'foo',
                    'anotherValue' => 42,
                ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $result->someValue);
        self::assertSame(42, $result->someOtherValue);
    }
}
