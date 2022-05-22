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
            'bar' => '42',
            'fiz' => '1337.404',
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

    public function test_shared_values_are_mapped_properly(): void
    {
        $source = [
            'foo' => 'foo',
            'bar' => '42',
            'fiz' => '1337.404',
        ];

        foreach (['array{foo: string, bar: int}', 'array{bar: int, fiz:float}'] as $signature) {
            try {
                $result = (new MapperBuilder())->mapper()->map($signature, $source);
            } catch (MappingError $error) {
                $this->mappingFail($error);
            }

            self::assertSame(42, $result['bar']);
        }
    }
}
