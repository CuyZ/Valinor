<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Other;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\TreeMapper;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;

final class SuperfluousKeysMappingTest extends IntegrationTestCase
{
    private TreeMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mapper = $this->mapperBuilder()->allowSuperfluousKeys()->mapper();
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

    public function test_single_property_node_can_be_mapped_with_superfluous_key(): void
    {
        try {
            $result = $this->mapper->map(SomeFooObject::class, [
                'foo' => 'foo',
                'bar' => 'bar',
            ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }
        self::assertSame('foo', $result->foo);
    }

    public function test_single_list_property_node_can_be_mapped_with_superfluous_key(): void
    {
        try {
            $result = $this->mapper->map(ObjectWithSingleListProperty::class, [
                'unrelated_key' => 'this-should-be-ignored-and-have-no-effect',
                'values' => [7, 8, 9],
            ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame([7, 8, 9], $result->values);
    }

    public function test_single_list_property_node_can_be_mapped_with_matching_key_and_without_superfluous_key(): void
    {
        try {
            $result = $this->mapper->map(ObjectWithSingleListProperty::class, [
                'values' => [7, 8, 9],
            ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame([7, 8, 9], $result->values);
    }

    public function test_single_list_property_node_can_be_mapped_(): void
    {
        try {
            $result = $this->mapper->map(ObjectWithSingleListProperty::class, [7, 8, 9]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame([7, 8, 9], $result->values);
    }

    public function test_object_with_one_array_property_can_be_mapped_when_superfluous_key_is_present(): void
    {
        $class = new class () {
            /** @var array<string> */
            public array $values;
        };

        try {
            $result = $this->mapper->map($class::class, [
                'values' => ['foo', 'bar'],
                'superfluous_key' => 'useless value',
            ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $result->values[0]);
        self::assertSame('bar', $result->values[1]);
    }
}

final class UnionOfBarAndFizAndFoo
{
    /** @var array<SomeBarAndFizObject|SomeFooObject> */
    public array $objects;
}

final class SomeFooObject
{
    public string $foo;
}

final class SomeBarAndFizObject
{
    public string $bar;

    public string $fiz;
}

final class ObjectWithSingleListProperty
{
    /** @var list<int> */
    public array $values;
}
