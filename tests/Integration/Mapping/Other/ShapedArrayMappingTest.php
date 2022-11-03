<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Other;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Tests\Integration\IntegrationTest;

final class ShapedArrayMappingTest extends IntegrationTest
{
    public function test_values_are_mapped_properly(): void
    {
        $source = [
            'foo' => 'foo',
            'bar' => 42,
            'fiz' => 1337.404,
        ];

        try {
            $result = (new MapperBuilder())->mapper()->map('array{foo: string, bar: int, fiz: float}', $source);
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
            $result = (new MapperBuilder())->mapper()->map('array{foo: string, bar: int, fiz: float}', $iterator);
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
            (new MapperBuilder())->mapper()->map('array{foo: string, bar: int}', ['foo' => 'foo']);
        } catch (MappingError $exception) {
            $error = $exception->node()->children()['bar']->messages()[0];

            self::assertSame('1655449641', $error->code());
            self::assertSame('Cannot be empty and must be filled with a value matching type `int`.', (string)$error);
        }
    }

    public function test_superfluous_values_throws_exception(): void
    {
        $source = [
            'foo' => 'foo',
            'bar' => 42,
            'fiz' => 1337.404,
        ];

        try {
            (new MapperBuilder())->mapper()->map('array{foo: string, bar: int}', $source);
        } catch (MappingError $exception) {
            $error = $exception->node()->messages()[0];

            self::assertSame('1655117782', $error->code());
            self::assertSame('Unexpected key(s) `fiz`, expected `foo`, `bar`.', (string)$error);
        }
    }
}
