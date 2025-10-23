<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Closure;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;

final class ArgumentsMappingTest extends IntegrationTestCase
{
    public function test_can_map_to_anonymous_function(): void
    {
        $function = fn (string $foo, int $bar): string => "$foo / $bar";

        try {
            $arguments = $this->mapperBuilder()->argumentsMapper()->mapArguments($function, [
                'foo' => 'foo',
                'bar' => 42,
            ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo / 42', $function(...$arguments));
    }

    public function test_can_map_to_class_method(): void
    {
        $object = new SomeClassWithMethods();

        try {
            $arguments = $this->mapperBuilder()->argumentsMapper()->mapArguments($object->somePublicMethod(...), [
                'foo' => 'foo',
                'bar' => 42,
            ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo / 42', $object->somePublicMethod(...$arguments));
    }

    public function test_can_map_to_class_static_method(): void
    {
        try {
            $arguments = $this->mapperBuilder()->argumentsMapper()->mapArguments(SomeClassWithMethods::somePublicStaticMethod(...), [
                'foo' => 'foo',
                'bar' => 42,
            ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo / 42', SomeClassWithMethods::somePublicStaticMethod(...$arguments));
    }

    public function test_can_map_to_function_with_single_argument(): void
    {
        $function = fn (string $foo): string => $foo;

        try {
            $arguments = $this->mapperBuilder()->argumentsMapper()->mapArguments($function, ['foo' => 'foo']);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $function(...$arguments));
    }

    public function test_can_map_to_single_argument_of_type_object_with_one_property_with_array_source(): void
    {
        $function = fn (SomeClassWithOneProperty $value): string => $value->foo . $value->foo;

        try {
            $arguments = $this->mapperBuilder()->argumentsMapper()->mapArguments($function, [
                'foo' => 'foo',
            ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foofoo', $function(...$arguments));
    }

    public function test_can_map_to_single_argument_of_type_object_with_one_property_with_array_source_sharing_names(): void
    {
        $function = fn (SomeClassWithOneProperty $foo): string => $foo->foo . $foo->foo;

        try {
            $arguments = $this->mapperBuilder()->argumentsMapper()->mapArguments($function, [
                'foo' => 'foo',
            ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foofoo', $function(...$arguments));
    }

    public function test_can_map_to_single_argument_of_type_object_with_one_property_with_scalar_source(): void
    {
        $function = fn (SomeClassWithOneProperty $value): string => $value->foo . $value->foo;

        try {
            $arguments = $this->mapperBuilder()->argumentsMapper()->mapArguments($function, 'foo');
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foofoo', $function(...$arguments));
    }

    public function test_can_map_to_single_argument_of_type_object_with_several_properties(): void
    {
        $function = fn (SomeClassWithTwoProperties $value): string => $value->foo . $value->bar;

        try {
            $arguments = $this->mapperBuilder()->argumentsMapper()->mapArguments($function, [
                'foo' => 'foo',
                'bar' => 42,
            ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo42', $function(...$arguments));
    }

    public function test_can_map_to_single_argument_of_type_object_with_several_properties_when_single_argument_shares_name_with_one_of_object_property_name(): void
    {
        $function = fn (SomeClassWithTwoProperties $foo): string => $foo->foo . $foo->bar;

        try {
            $arguments = $this->mapperBuilder()->argumentsMapper()->mapArguments($function, [
                'foo' => 'foo',
                'bar' => 42,
            ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo42', $function(...$arguments));
    }

    public function test_can_map_to_single_argument_of_type_shaped_array(): void
    {
        $function =
            /**
             * @param array{foo: string, bar: int} $value
             */
            fn (array $value): string => $value['foo'] . $value['bar']; // @phpstan-ignore binaryOp.invalid (we cannot set closure parameters / see https://github.com/phpstan/phpstan/issues/3770)

        try {
            $arguments = $this->mapperBuilder()->argumentsMapper()->mapArguments($function, [
                'value' => [
                    'foo' => 'foo',
                    'bar' => 42,
                ],
            ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo42', $function(...$arguments));
    }

    public function test_map_to_single_argument_of_type_shaped_array_does_not_accept_flattened_values(): void
    {
        $function =
            /**
             * @param array{foo: string, bar: int} $value
             */
            fn (array $value): string => $value['foo'] . $value['bar']; // @phpstan-ignore binaryOp.invalid (we cannot set closure parameters / see https://github.com/phpstan/phpstan/issues/3770)

        try {
            $this->mapperBuilder()->argumentsMapper()->mapArguments($function, [
                'foo' => 'foo',
                'bar' => 42,
            ]);
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, [
                'value' => '[missing_value] Cannot be empty and must be filled with a value matching `array{foo: string, bar: int}`.',
                'foo' => '[unexpected_key] Unexpected key `foo`.',
                'bar' => '[unexpected_key] Unexpected key `bar`.',
            ]);
        }
    }

    public function test_invalid_source_with_one_error_throws_mapping_error(): void
    {
        $function = fn (string $foo, int $bar): string => "$foo / $bar";

        try {
            $this->mapperBuilder()->argumentsMapper()->mapArguments($function, [
                'foo' => false,
                'bar' => false,
            ]);
        } catch (MappingError $exception) {
            self::assertMatchesRegularExpression('/Could not map arguments of `[^`]+` with value array{foo: false, bar: false}. A total of 2 errors were encountered./', $exception->getMessage());

            self::assertMappingErrors($exception, [
                'foo' => '[invalid_string] Value false is not a valid string.',
                'bar' => '[invalid_integer] Value false is not a valid integer.',
            ]);
        }
    }

    public function test_invalid_source_with_two_errors_throws_mapping_error(): void
    {
        $function = fn (string $foo, int $bar): string => "$foo / $bar";

        try {
            $this->mapperBuilder()->argumentsMapper()->mapArguments($function, [
                'foo' => false,
                'bar' => 42,
            ]);
        } catch (MappingError $exception) {
            self::assertMatchesRegularExpression('/Could not map arguments of `[^`]+`. An error occurred at path foo: Value false is not a valid string./', $exception->getMessage());

            self::assertMappingErrors($exception, [
                'foo' => '[invalid_string] Value false is not a valid string.',
            ]);
        }
    }
}

final class SomeClassWithMethods
{
    public function somePublicMethod(string $foo, int $bar): string
    {
        return "$foo / $bar";
    }

    public static function somePublicStaticMethod(string $foo, int $bar): string
    {
        return "$foo / $bar";
    }
}

final class SomeClassWithOneProperty
{
    public string $foo;
}

final class SomeClassWithTwoProperties
{
    public string $foo;
    public int $bar;
}
