<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Other;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;

final class ShapedArrayMappingTest extends IntegrationTestCase
{
    public function test_values_are_mapped_properly(): void
    {
        $source = [
            'foo' => 'foo',
            'bar' => 42,
            'fiz' => 1337.404,
        ];

        try {
            $result = $this->mapperBuilder()->mapper()->map('array{foo: string, bar: int, fiz: float}', $source);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $result['foo']);
        self::assertSame(42, $result['bar']);
        self::assertSame(1337.404, $result['fiz']);
    }

    public function test_mapping_from_iterable_to_shaped_array_works_properly(): void
    {
        $iterator = (static function () {
            yield 'foo' => 'foo';
            yield 'bar' => 42;
            yield 'fiz' => 1337.404;
        })();

        try {
            $result = $this->mapperBuilder()->mapper()->map('array{foo: string, bar: int, fiz: float}', $iterator);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $result['foo']);
        self::assertSame(42, $result['bar']);
        self::assertSame(1337.404, $result['fiz']);
    }

    public function test_missing_element_throws_exception(): void
    {
        try {
            $this->mapperBuilder()->mapper()->map('array{foo: string, bar: int}', ['foo' => 'foo']);
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, [
                'bar' => "[invalid_integer] Value *missing* is not a valid integer.",
            ]);
        }
    }

    public function test_superfluous_values_throws_exception_and_keeps_nested_errors(): void
    {
        $source = [
            'foo' => 404,
            'bar' => 42,
            'fiz' => 1337.404,
        ];

        try {
            $this->mapperBuilder()->mapper()->map('array{foo: string, bar: int}', $source);
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, [
                'foo' => '[invalid_string] Value 404 is not a valid string.',
                'fiz' => '[unexpected_key] Unexpected key `fiz`.',
            ]);
        }
    }
}
