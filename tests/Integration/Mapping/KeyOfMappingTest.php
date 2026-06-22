<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;

final class KeyOfMappingTest extends IntegrationTestCase
{
    public function test_can_map_key_of_pure_enum(): void
    {
        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map('key-of<' . SomeEnumForKeyOf::class . '>', 'FOO');
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('FOO', $result);
    }

    public function test_can_map_key_of_backed_string_enum(): void
    {
        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map('key-of<' . SomeBackedStringEnumForKeyOf::class . '>', 'FOO');
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('FOO', $result);
    }

    public function test_can_map_key_of_backed_integer_enum(): void
    {
        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map('key-of<' . SomeBackedIntegerEnumForKeyOf::class . '>', 'FOO');
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('FOO', $result);
    }

    public function test_can_map_key_of_enum_with_one_case(): void
    {
        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map('key-of<' . SomeEnumForKeyOfWithOneCase::class . '>', 'FOO');
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('FOO', $result);
    }

    public function test_can_map_key_of_array(): void
    {
        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map('array<key-of<' . SomeBackedStringEnumForKeyOf::class . '>, string>', ['FOO' => 'value']);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(['FOO' => 'value'], $result);
    }

    public function test_key_of_enum_mapping_error(): void
    {
        try {
            $this->mapperBuilder()
                ->mapper()
                ->map('key-of<' . SomeBackedStringEnumForKeyOf::class . '>', 'INVALID');
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, [
                '*root*' => "[cannot_resolve_type_from_union] Value 'INVALID' does not match any of 'FOO', 'FOZ', 'BAZ'.",
            ]);
        }
    }

    public function test_can_map_key_of_shaped_list(): void
    {
        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map('key-of<list{string, int}>', 0);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(0, $result);
    }

    public function test_key_of_shaped_list_mapping_error(): void
    {
        try {
            $this->mapperBuilder()
                ->mapper()
                ->map('key-of<list{string, int}>', 5);
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, [
                '*root*' => '[cannot_resolve_type_from_union] Value 5 does not match any of 0, 1.',
            ]);
        }
    }
}

enum SomeEnumForKeyOf
{
    case FOO;
    case FOZ;
    case BAZ;
}

enum SomeEnumForKeyOfWithOneCase
{
    case FOO;
}

enum SomeBackedStringEnumForKeyOf: string
{
    case FOO = 'foo';
    case FOZ = 'foz';
    case BAZ = 'baz';
}

enum SomeBackedIntegerEnumForKeyOf: int
{
    case FOO = 42;
    case FOZ = 404;
    case BAZ = 1337;
}
