<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Closure;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;

final class ArgumentsMappingTest extends IntegrationTestCase
{
    public function test_can_map_to_anonymous_function(): void
    {
        $function = fn (string $foo, int $bar): string => "$foo / $bar";

        try {
            $arguments = (new MapperBuilder())->argumentsMapper()->mapArguments($function, [
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
            $arguments = (new MapperBuilder())->argumentsMapper()->mapArguments($object->somePublicMethod(...), [
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
            $arguments = (new MapperBuilder())->argumentsMapper()->mapArguments(SomeClassWithMethods::somePublicStaticMethod(...), [
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
            $arguments = (new MapperBuilder())->argumentsMapper()->mapArguments($function, ['foo' => 'foo']);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $function(...$arguments));
    }

    public function test_invalid_source_with_one_error_throws_mapping_error(): void
    {
        $function = fn (string $foo, int $bar): string => "$foo / $bar";

        try {
            (new MapperBuilder())->argumentsMapper()->mapArguments($function, [
                'foo' => false,
                'bar' => false,
            ]);
        } catch (MappingError $exception) {
            self::assertMatchesRegularExpression('/Could not map arguments of `[^`]+` with value array{foo: false, bar: false}. A total of 2 errors were encountered./', $exception->getMessage());

            self::assertSame('Value false is not a valid string.', (string)$exception->node()->children()['foo']->messages()[0]);
            self::assertSame('Value false is not a valid integer.', (string)$exception->node()->children()['bar']->messages()[0]);
        }
    }

    public function test_invalid_source_with_two_errors_throws_mapping_error(): void
    {
        $function = fn (string $foo, int $bar): string => "$foo / $bar";

        try {
            (new MapperBuilder())->argumentsMapper()->mapArguments($function, [
                'foo' => false,
                'bar' => 42,
            ]);
        } catch (MappingError $exception) {
            self::assertMatchesRegularExpression('/Could not map arguments of `[^`]+`. An error occurred at path foo: Value false is not a valid string./', $exception->getMessage());

            self::assertSame('Value false is not a valid string.', (string)$exception->node()->children()['foo']->messages()[0]);
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
