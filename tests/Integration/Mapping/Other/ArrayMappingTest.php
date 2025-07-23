<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Other;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;
use stdClass;

final class ArrayMappingTest extends IntegrationTestCase
{
    public function test_map_to_array_of_scalars_works_properly(): void
    {
        $source = ['foo', 'bar', 'baz'];

        try {
            $result = $this->mapperBuilder()->mapper()->map('string[]', $source);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame($source, $result);
    }

    public function test_map_to_array_with_union_string_key_works_properly(): void
    {
        $source = ['foo' => 'foo', 'bar' => 'bar'];

        try {
            $result = $this->mapperBuilder()->mapper()->map("array<'foo'|'bar', string>", $source);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame($source, $result);
    }

    public function test_map_to_array_with_union_integer_key_works_properly(): void
    {
        $source = [42 => 'foo', 1337 => 'bar'];

        try {
            $result = $this->mapperBuilder()->mapper()->map('array<42|1337, string>', $source);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame($source, $result);
    }

    public function test_map_to_array_with_positive_integer_key_works_properly(): void
    {
        $source = [42 => 'foo', 1337 => 'bar'];

        try {
            $result = $this->mapperBuilder()->mapper()->map('array<positive-int, string>', $source);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame($source, $result);
    }

    public function test_map_to_array_with_negative_integer_key_works_properly(): void
    {
        $source = [-42 => 'foo', -1337 => 'bar'];

        try {
            $result = $this->mapperBuilder()->mapper()->map('array<negative-int, string>', $source);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame($source, $result);
    }

    public function test_map_to_array_with_integer_range_key_works_properly(): void
    {
        $source = [-42 => 'foo', 42 => 'foo', 1337 => 'bar'];

        try {
            $result = $this->mapperBuilder()->mapper()->map('array<int<-42, 1337>, string>', $source);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame($source, $result);
    }

    public function test_map_to_array_with_non_empty_string_key_works_properly(): void
    {
        $source = ['foo' => 'foo', 'bar' => 'bar'];

        try {
            $result = $this->mapperBuilder()->mapper()->map('array<non-empty-string, string>', $source);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame($source, $result);
    }

    public function test_map_to_array_with_class_string_key_works_properly(): void
    {
        $source = [stdClass::class => 'foo'];

        try {
            $result = $this->mapperBuilder()->mapper()->map('array<class-string, string>', $source);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame($source, $result);
    }

    public function test_value_with_invalid_integer_key_type_throws_exception(): void
    {
        try {
            $this->mapperBuilder()->mapper()->map('array<int, string>', ['foo' => 'foo']);
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, [
                'foo' => "[invalid_array_key] Key 'foo' does not match type `int`.",
            ]);
        }
    }

    public function test_value_with_invalid_positive_integer_key_type_throws_exception(): void
    {
        try {
            $this->mapperBuilder()->mapper()->map('array<positive-int, string>', [-42 => 'foo']);
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, [
                '-42' => "[invalid_array_key] Key -42 does not match type `positive-int`.",
            ]);
        }
    }

    public function test_value_with_invalid_negative_integer_key_type_throws_exception(): void
    {
        try {
            $this->mapperBuilder()->mapper()->map('array<negative-int, string>', [42 => 'foo']);
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, [
                '42' => '[invalid_array_key] Key 42 does not match type `negative-int`.',
            ]);
        }
    }

    public function test_value_with_invalid_integer_range_key_type_throws_exception(): void
    {
        try {
            $this->mapperBuilder()->mapper()->map('array<int<-42, 1337>, string>', [-404 => 'foo']);
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, [
                '-404' => '[invalid_array_key] Key -404 does not match type `int<-42, 1337>`.',
            ]);
        }
    }

    public function test_value_with_invalid_union_string_key_type_throws_exception(): void
    {
        try {
            $this->mapperBuilder()->mapper()->map("array<'foo'|'bar', string>", ['baz' => 'baz']);
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, [
                'baz' => "[invalid_array_key] Key 'baz' does not match type `'foo'|'bar'`.",
            ]);
        }
    }

    public function test_value_with_invalid_union_integer_key_type_throws_exception(): void
    {
        try {
            $this->mapperBuilder()->mapper()->map('array<42|1337, string>', [404 => 'baz']);
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, [
                '404' => '[invalid_array_key] Key 404 does not match type `42|1337`.',
            ]);
        }
    }

    public function test_value_with_invalid_non_empty_string_key_type_throws_exception(): void
    {
        try {
            $this->mapperBuilder()->mapper()->map('array<non-empty-string, string>', ['' => 'foo']);
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, [
                '' => "[invalid_array_key] Key '' does not match type `non-empty-string`.",
            ]);
        }
    }

    public function test_value_with_invalid_class_string_key_type_throws_exception(): void
    {
        try {
            $this->mapperBuilder()->mapper()->map('array<class-string, string>', ['foo bar' => 'foo']);
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, [
                'foo bar' => "[invalid_array_key] Key 'foo bar' does not match type `class-string`.",
            ]);
        }
    }
}
