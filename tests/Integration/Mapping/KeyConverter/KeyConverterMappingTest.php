<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\KeyConverter;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Tree\Exception\KeyConverterHasInvalidStringParameter;
use CuyZ\Valinor\Mapper\Tree\Exception\KeyConverterHasNoParameter;
use CuyZ\Valinor\Mapper\Tree\Exception\KeyConverterHasTooManyParameters;
use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use CuyZ\Valinor\Tests\Fake\Mapper\Tree\Message\FakeErrorMessage;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;
use DomainException;
use Throwable;

use function str_starts_with;
use function strtolower;
use function substr;

final class KeyConverterMappingTest extends IntegrationTestCase
{
    public function test_converter_maps_source_keys_to_shaped_array_elements(): void
    {
        try {
            $result = $this->mapperBuilder()
                ->registerKeyConverter(fn (string $key): string => strtolower($key))
                ->mapper()
                ->map('array{foo: string, bar: int}', ['FOO' => 'hello', 'BAR' => 42]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('hello', $result['foo']);
        self::assertSame(42, $result['bar']);
    }

    public function test_converter_maps_source_keys_to_object_properties(): void
    {
        $class = new class () {
            public string $name;
            public int $age;
        };

        try {
            $result = $this->mapperBuilder()
                ->registerKeyConverter(fn (string $key): string => strtolower($key))
                ->mapper()
                ->map($class::class, ['NAME' => 'John', 'AGE' => 42]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('John', $result->name);
        self::assertSame(42, $result->age);
    }

    public function test_converter_maps_source_keys_to_single_property_object(): void
    {
        $class = new class () {
            public string $value;
        };

        try {
            $result = $this->mapperBuilder()
                ->registerKeyConverter(fn (string $key): string => strtolower($key))
                ->mapper()
                ->map($class::class, ['VALUE' => 'hello']);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('hello', $result->value);
    }

    public function test_converter_maps_scalar_to_single_property_object(): void
    {
        $class = new class () {
            public string $value;
        };

        try {
            $result = $this->mapperBuilder()
                ->registerKeyConverter(fn (string $key): string => strtolower($key))
                ->mapper()
                ->map($class::class, 'hello');
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('hello', $result->value);
    }

    public function test_error_path_uses_original_source_key_for_shaped_array(): void
    {
        try {
            $this->mapperBuilder()
                ->registerKeyConverter(fn (string $key): string => strtolower($key))
                ->mapper()
                ->map('array{foo: string}', ['FOO' => 42]);

            self::fail('Expected MappingError');
        } catch (MappingError $error) {
            $this->assertMappingErrors($error, [
                'FOO' => '[invalid_string] Value 42 is not a valid string.',
            ]);
        }
    }

    public function test_error_path_uses_original_source_key_for_object(): void
    {
        $class = new class () {
            public string $value;
        };

        try {
            $this->mapperBuilder()
                ->registerKeyConverter(fn (string $key): string => strtolower($key))
                ->mapper()
                ->map($class::class, ['VALUE' => 42]);

            self::fail('Expected MappingError');
        } catch (MappingError $error) {
            $this->assertMappingErrors($error, [
                'VALUE' => '[invalid_string] Value 42 is not a valid string.',
            ]);
        }
    }

    public function test_error_path_uses_original_source_key_in_nested_object(): void
    {
        $class = new class () {
            public string $value;
        };

        try {
            $this->mapperBuilder()
                ->registerKeyConverter(fn (string $key): string => strtolower($key))
                ->mapper()
                ->map('array{parent: ' . $class::class . '}', [
                    'PARENT' => ['VALUE' => 42],
                ]);

            self::fail('Expected MappingError');
        } catch (MappingError $error) {
            $this->assertMappingErrors($error, [
                'PARENT.VALUE' => '[invalid_string] Value 42 is not a valid string.',
            ]);
        }
    }

    public function test_superfluous_keys_show_original_source_key(): void
    {
        try {
            $this->mapperBuilder()
                ->registerKeyConverter(fn (string $key): string => strtolower($key))
                ->mapper()
                ->map('array{foo: string}', [
                    'FOO' => 'hello',
                    'EXTRA' => 'bar',
                ]);

            self::fail('Expected MappingError');
        } catch (MappingError $error) {
            $this->assertMappingErrors($error, [
                'EXTRA' => '[unexpected_key] Unexpected key `EXTRA`.',
            ]);
        }
    }

    public function test_multiple_converters_are_applied_as_pipeline(): void
    {
        try {
            $result = $this->mapperBuilder()
                ->registerKeyConverter(
                    fn (string $key): string => str_starts_with($key, 'input_') ? substr($key, 6) : $key,
                )
                ->registerKeyConverter(
                    fn (string $key): string => strtolower($key),
                )
                ->mapper()
                ->map('array{foo: string}', ['input_FOO' => 'hello']);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('hello', $result['foo']);
    }

    public function test_multiple_converters_error_path_uses_original_key(): void
    {
        try {
            $this->mapperBuilder()
                ->registerKeyConverter(
                    fn (string $key): string => str_starts_with($key, 'input_') ? substr($key, 6) : $key,
                )
                ->registerKeyConverter(
                    fn (string $key): string => strtolower($key),
                )
                ->mapper()
                ->map('array{foo: string}', ['input_FOO' => 42]);

            self::fail('Expected MappingError');
        } catch (MappingError $error) {
            $this->assertMappingErrors($error, [
                'input_FOO' => '[invalid_string] Value 42 is not a valid string.',
            ]);
        }
    }

    public function test_optional_element_with_missing_converted_key_is_skipped(): void
    {
        try {
            $result = $this->mapperBuilder()
                ->registerKeyConverter(fn (string $key): string => strtolower($key))
                ->mapper()
                ->map('array{foo: string, bar?: int}', ['FOO' => 'hello']);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('hello', $result['foo']);
        self::assertArrayNotHasKey('bar', $result);
    }

    public function test_converter_handles_integer_source_keys_for_shaped_array(): void
    {
        // Integer keys from array_keys() must be cast to string before
        // being passed to the converter.
        try {
            $result = $this->mapperBuilder()
                ->registerKeyConverter(fn (string $key): string => "key_$key")
                ->mapper()
                ->map('array{key_0: string, key_1: int}', ['hello', 42]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('hello', $result['key_0']);
        self::assertSame(42, $result['key_1']);
    }

    public function test_converter_handles_integer_source_keys_for_object(): void
    {
        $class = new class () {
            public string $key_0;
        };

        try {
            $result = $this->mapperBuilder()
                ->registerKeyConverter(fn (string $key): string => "key_{$key}")
                ->mapper()
                ->map($class::class, ['hello']);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('hello', $result->key_0);
    }

    public function test_duplicate_converted_keys_uses_last_source_key(): void
    {
        // Both 'FOO' and 'foo' convert to 'foo'. Last one wins.
        try {
            $result = $this->mapperBuilder()
                ->registerKeyConverter(fn (string $key): string => strtolower($key))
                ->mapper()
                ->map('array{foo: string}', ['FOO' => 'first', 'foo' => 'second']);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('second', $result['foo']);
    }

    public function test_key_converter_with_no_parameter_throws_exception(): void
    {
        $this->expectException(KeyConverterHasNoParameter::class);
        $this->expectExceptionMessageMatches('/The key converter `.*` has no parameter to convert the key, a string parameter is required\./');

        $this->mapperBuilder()
            ->registerKeyConverter(fn (): string => 'foo')
            ->mapper()
            ->map('array{foo: string}', ['FOO' => 'hello']);
    }

    public function test_key_converter_with_too_many_parameters_throws_exception(): void
    {
        $this->expectException(KeyConverterHasTooManyParameters::class);
        $this->expectExceptionMessageMatches('/Key converter must have only one parameter, 2 given for `.*`\./');

        $this->mapperBuilder()
            ->registerKeyConverter(fn (string $key, string $extra): string => $key) // @phpstan-ignore argument.type
            ->mapper()
            ->map('array{foo: string}', ['FOO' => 'hello']);
    }

    public function test_key_converter_with_invalid_parameter_type_throws_exception(): void
    {
        $this->expectException(KeyConverterHasInvalidStringParameter::class);
        $this->expectExceptionMessageMatches('/Key converter\'s parameter must be a string, `int` given for `.*`\./');

        $this->mapperBuilder()
            ->registerKeyConverter(fn (int $key): string => (string)$key) // @phpstan-ignore argument.type
            ->mapper()
            ->map('array{foo: string}', ['FOO' => 'hello']);
    }

    public function test_key_converter_exception_not_filtered_is_not_caught(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('bad key');

        $this->mapperBuilder()
            ->registerKeyConverter(fn (string $key): string => throw new DomainException('bad key'))
            ->mapper()
            ->map('array{foo: string}', ['FOO' => 'hello']);
    }

    public function test_key_converter_exception_filtered_is_caught_and_added_to_mapping_errors(): void
    {
        try {
            $this->mapperBuilder()
                ->registerKeyConverter(fn (string $key): string => throw new DomainException('bad key', 1771930555))
                ->filterExceptions(function (Throwable $exception): ErrorMessage {
                    if ($exception instanceof DomainException) {
                        return new FakeErrorMessage($exception->getMessage(), $exception->getCode());
                    }

                    throw $exception;
                })
                ->mapper()
                ->map('array{foo: string}', ['FOO' => 'hello']);

            self::fail('Expected MappingError');
        } catch (MappingError $error) {
            $this->assertMappingErrors($error, [
                'FOO' => '[1771930555] bad key',
            ], assertErrorsBodiesAreRegistered: false);
        }
    }

    public function test_key_converter_exception_implementing_message_is_used_directly(): void
    {
        try {
            $this->mapperBuilder()
                ->registerKeyConverter(
                    fn (string $key): string => throw new FakeErrorMessage('message error', 1771930568), // @phpstan-ignore argument.type
                )
                ->mapper()
                ->map('array{foo: string}', ['FOO' => 'hello']);

            self::fail('Expected MappingError');
        } catch (MappingError $error) {
            $this->assertMappingErrors($error, [
                'FOO' => '[1771930568] message error',
            ], assertErrorsBodiesAreRegistered: false);
        }
    }

    public function test_key_converter_exception_error_path_uses_original_key(): void
    {
        try {
            $this->mapperBuilder()
                // @phpstan-ignore argument.type
                ->registerKeyConverter(function (string $key): string {
                    if ($key === 'BAD_KEY') {
                        throw new FakeErrorMessage('invalid key', 1771930575);
                    }

                    return strtolower($key);
                })
                ->mapper()
                ->map('array{foo: string, bar: int}', ['FOO' => 'hello', 'BAD_KEY' => 42]);

            self::fail('Expected MappingError');
        } catch (MappingError $error) {
            $this->assertMappingErrors($error, [
                'BAD_KEY' => '[1771930575] invalid key',
            ], assertErrorsBodiesAreRegistered: false);
        }
    }

    public function test_key_converter_exception_on_object_is_caught_with_filter(): void
    {
        $class = new class () {
            public string $name;
            public int $age;
        };

        try {
            $this->mapperBuilder()
                ->registerKeyConverter(fn (string $key): string => throw new DomainException('bad key', 1771930579))
                ->filterExceptions(function (Throwable $exception): ErrorMessage {
                    if ($exception instanceof DomainException) {
                        return new FakeErrorMessage($exception->getMessage(), $exception->getCode());
                    }

                    throw $exception;
                })
                ->mapper()
                ->map($class::class, ['NAME' => 'John', 'AGE' => 42]);

            self::fail('Expected MappingError');
        } catch (MappingError $error) {
            $this->assertMappingErrors($error, [
                'NAME' => '[1771930579] bad key',
                'AGE' => '[1771930579] bad key',
            ], assertErrorsBodiesAreRegistered: false);
        }
    }
}
