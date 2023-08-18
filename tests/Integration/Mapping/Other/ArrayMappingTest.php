<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Other;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Tests\Integration\IntegrationTest;
use stdClass;

final class ArrayMappingTest extends IntegrationTest
{
    public function test_map_to_array_of_scalars_works_properly(): void
    {
        $source = ['foo', 'bar', 'baz'];

        try {
            $result = (new MapperBuilder())->mapper()->map('string[]', $source);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame($source, $result);
    }

    public function test_map_to_array_with_union_string_key_works_properly(): void
    {
        $source = ['foo' => 'foo', 'bar' => 'bar'];

        try {
            $result = (new MapperBuilder())->mapper()->map("array<'foo'|'bar', string>", $source);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame($source, $result);
    }

    public function test_map_to_array_with_union_integer_key_works_properly(): void
    {
        $source = [42 => 'foo', 1337 => 'bar'];

        try {
            $result = (new MapperBuilder())->mapper()->map('array<42|1337, string>', $source);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame($source, $result);
    }

    public function test_map_to_array_with_positive_integer_key_works_properly(): void
    {
        $source = [42 => 'foo', 1337 => 'bar'];

        try {
            $result = (new MapperBuilder())->mapper()->map('array<positive-int, string>', $source);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame($source, $result);
    }

    public function test_map_to_array_with_negative_integer_key_works_properly(): void
    {
        $source = [-42 => 'foo', -1337 => 'bar'];

        try {
            $result = (new MapperBuilder())->mapper()->map('array<negative-int, string>', $source);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame($source, $result);
    }

    public function test_map_to_array_with_integer_range_key_works_properly(): void
    {
        $source = [-42 => 'foo', 42 => 'foo', 1337 => 'bar'];

        try {
            $result = (new MapperBuilder())->mapper()->map('array<int<-42, 1337>, string>', $source);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame($source, $result);
    }

    public function test_map_to_array_with_non_empty_string_key_works_properly(): void
    {
        $source = ['foo' => 'foo', 'bar' => 'bar'];

        try {
            $result = (new MapperBuilder())->mapper()->map('array<non-empty-string, string>', $source);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame($source, $result);
    }

    public function test_map_to_array_with_class_string_key_works_properly(): void
    {
        $source = [stdClass::class => 'foo'];

        try {
            $result = (new MapperBuilder())->mapper()->map('array<class-string, string>', $source);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame($source, $result);
    }

    public function test_value_with_invalid_integer_key_type_throws_exception(): void
    {
        try {
            (new MapperBuilder())->mapper()->map('array<int, string>', ['foo' => 'foo']);
        } catch (MappingError $exception) {
            $error = $exception->node()->children()['foo']->messages()[0];

            self::assertSame("Key 'foo' does not match type `int`.", (string)$error);
        }
    }

    public function test_value_with_invalid_positive_integer_key_type_throws_exception(): void
    {
        try {
            (new MapperBuilder())->mapper()->map('array<positive-int, string>', [-42 => 'foo']);
        } catch (MappingError $exception) {
            $error = $exception->node()->children()[-42]->messages()[0];

            self::assertSame("Key -42 does not match type `positive-int`.", (string)$error);
        }
    }

    public function test_value_with_invalid_negative_integer_key_type_throws_exception(): void
    {
        try {
            (new MapperBuilder())->mapper()->map('array<negative-int, string>', [42 => 'foo']);
        } catch (MappingError $exception) {
            $error = $exception->node()->children()[42]->messages()[0];

            self::assertSame("Key 42 does not match type `negative-int`.", (string)$error);
        }
    }

    public function test_value_with_invalid_integer_range_key_type_throws_exception(): void
    {
        try {
            (new MapperBuilder())->mapper()->map('array<int<-42, 1337>, string>', [-404 => 'foo']);
        } catch (MappingError $exception) {
            $error = $exception->node()->children()[-404]->messages()[0];

            self::assertSame("Key -404 does not match type `int<-42, 1337>`.", (string)$error);
        }
    }

    public function test_value_with_invalid_union_string_key_type_throws_exception(): void
    {
        try {
            (new MapperBuilder())->mapper()->map("array<'foo'|'bar', string>", ['baz' => 'baz']);
        } catch (MappingError $exception) {
            $error = $exception->node()->children()['baz']->messages()[0];

            self::assertSame("Key 'baz' does not match type `'foo'|'bar'`.", (string)$error);
        }
    }

    public function test_value_with_invalid_union_integer_key_type_throws_exception(): void
    {
        try {
            (new MapperBuilder())->mapper()->map('array<42|1337, string>', [404 => 'baz']);
        } catch (MappingError $exception) {
            $error = $exception->node()->children()[404]->messages()[0];

            self::assertSame("Key 404 does not match type `42|1337`.", (string)$error);
        }
    }

    public function test_value_with_invalid_non_empty_string_key_type_throws_exception(): void
    {
        try {
            (new MapperBuilder())->mapper()->map('array<non-empty-string, string>', ['' => 'foo']);
        } catch (MappingError $exception) {
            $error = $exception->node()->children()['']->messages()[0];

            self::assertSame("Key '' does not match type `non-empty-string`.", (string)$error);
        }
    }

    public function test_value_with_invalid_class_string_key_type_throws_exception(): void
    {
        try {
            (new MapperBuilder())->mapper()->map('array<class-string, string>', ['foo bar' => 'foo']);
        } catch (MappingError $exception) {
            $error = $exception->node()->children()['foo bar']->messages()[0];

            self::assertSame("Key 'foo bar' does not match type `class-string`.", (string)$error);
        }
    }
}
