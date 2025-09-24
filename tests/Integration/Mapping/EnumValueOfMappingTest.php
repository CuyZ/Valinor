<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;

final class EnumValueOfMappingTest extends IntegrationTestCase
{
    public function test_can_map_value_of_string_enum(): void
    {
        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map('value-of<' . SomeStringEnumForValueOf::class . '>', SomeStringEnumForValueOf::FOO->value);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(SomeStringEnumForValueOf::FOO->value, $result);
    }

    public function test_can_map_value_of_enum_with_one_case(): void
    {
        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map('value-of<' . SomeStringEnumForValueOfWithOneCase::class . '>', SomeStringEnumForValueOf::FOO->value);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(SomeStringEnumForValueOf::FOO->value, $result); // @phpstan-ignore-line
    }

    public function test_can_map_value_of_integer_enum(): void
    {
        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map('value-of<' . SomeIntegerEnumForValueOf::class . '>', SomeIntegerEnumForValueOf::FOO->value);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(SomeIntegerEnumForValueOf::FOO->value, $result);
    }

    public function test_array_keys_using_value_of(): void
    {
        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map('array<value-of<' . SomeStringEnumForValueOf::class . '>, string>', [SomeStringEnumForValueOf::FOO->value => 'foo']);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame([SomeStringEnumForValueOf::FOO->value => 'foo'], $result);
    }

    public function test_array_keys_using_value_of_error(): void
    {
        try {
            $this->mapperBuilder()
                ->mapper()
                ->map('array<value-of<' . SomeStringEnumForValueOf::class . '>, string>', ['oof' => 'foo']);
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, [
               'oof' => "[invalid_array_key] Key 'oof' does not match type `'FOO'|'FOZ'|'BAZ'`.",
            ]);
        }
    }
}

enum SomeStringEnumForValueOf: string
{
    case FOO = 'FOO';
    case FOZ = 'FOZ';
    case BAZ = 'BAZ';
}

enum SomeStringEnumForValueOfWithOneCase: string
{
    case FOO = 'FOO';
}

enum SomeIntegerEnumForValueOf: int
{
    case FOO = 42;
    case FOZ = 404;
    case BAZ = 1337;
}
