<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;

final class ArrayValueOfMappingTest extends IntegrationTestCase
{
    public function test_can_map_value_of_array(): void
    {
        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map('value-of<array<string, int>>', 42);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(42, $result);
    }

    public function test_can_map_value_of_list(): void
    {
        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map('value-of<list<string>>', 'hello');
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('hello', $result);
    }

    public function test_can_map_value_of_shaped_array(): void
    {
        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map('value-of<array{foo: string, bar: int}>', 'hello');
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('hello', $result);
    }

    public function test_can_map_array_values_using_value_of(): void
    {
        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map('array<string, value-of<' . SomeStringEnumForValueOfArray::class . '>>', [SomeStringEnumForValueOfArray::FOO->value => SomeStringEnumForValueOfArray::FOO->value]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame([SomeStringEnumForValueOfArray::FOO->value => SomeStringEnumForValueOfArray::FOO->value], $result);
    }

    public function test_value_of_shaped_array_mapping_error(): void
    {
        try {
            $this->mapperBuilder()
                ->mapper()
                ->map('value-of<array{foo: string, bar: int}>', true);
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, [
                '*root*' => "[cannot_resolve_type_from_union] Value true does not match any of `string`, `int`.",
            ]);
        }
    }

    public function test_can_map_value_of_shaped_list(): void
    {
        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map('value-of<list{string, int}>', 'hello');
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('hello', $result);
    }

    public function test_value_of_shaped_list_mapping_error(): void
    {
        try {
            $this->mapperBuilder()
                ->mapper()
                ->map('value-of<list{string, int}>', true);
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, [
                '*root*' => "[cannot_resolve_type_from_union] Value true does not match any of `string`, `int`.",
            ]);
        }
    }
}

enum SomeStringEnumForValueOfArray: string
{
    case FOO = 'FOO';
    case BAR = 'BAR';
}
