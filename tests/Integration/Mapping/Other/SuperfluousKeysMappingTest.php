<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Other;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\TreeMapper;
use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Tests\Integration\IntegrationTest;

final class SuperfluousKeysMappingTest extends IntegrationTest
{
    private TreeMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mapper = (new MapperBuilder())->allowSuperfluousKeys()->mapper();
    }

    public function test_superfluous_shaped_array_values_are_mapped_properly(): void
    {
        $source = [
            'foo' => 'foo',
            'bar' => 42,
            'fiz' => 1337.404,
        ];

        foreach (['array{foo: string, bar: int}', 'array{bar: int, fiz: float}'] as $signature) {
            try {
                $result = $this->mapper->map($signature, $source);
            } catch (MappingError $error) {
                $this->mappingFail($error);
            }

            self::assertSame(42, $result['bar']);
        }
    }

    public function test_source_matching_two_unions_maps_the_one_with_most_arguments(): void
    {
        try {
            $result = $this->mapper->map(UnionOfBarAndFizAndFoo::class, [
                ['foo' => 'foo', 'bar' => 'bar', 'fiz' => 'fiz'],
            ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        $object = $result->objects[0];

        self::assertInstanceOf(SomeBarAndFizObject::class, $object);
        self::assertSame('bar', $object->bar);
        self::assertSame('fiz', $object->fiz);
    }
}
