<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Object;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Integration\IntegrationTest;
use CuyZ\Valinor\Tests\Integration\Mapping\Fixture\NativeUnionOfObjects;

final class UnionOfObjectsMappingTest extends IntegrationTest
{
    /**
     * @requires PHP >= 8
     */
    public function test_object_type_is_narrowed_correctly_for_simple_case(): void
    {
        try {
            $resultFoo = $this->mapperBuilder->mapper()->map(NativeUnionOfObjects::class, [
                'foo' => 'foo'
            ]);
            $resultBar = $this->mapperBuilder->mapper()->map(NativeUnionOfObjects::class, [
                'bar' => 'bar'
            ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertInstanceOf(\CuyZ\Valinor\Tests\Integration\Mapping\Fixture\SomeFooObject::class, $resultFoo->object);
        self::assertInstanceOf(\CuyZ\Valinor\Tests\Integration\Mapping\Fixture\SomeBarObject::class, $resultBar->object);
    }

    public function test_object_type_is_narrowed_correctly_for_simple_array_case(): void
    {
        try {
            $result = $this->mapperBuilder->mapper()->map(UnionOfFooAndBar::class, [
                'foo' => ['foo' => 'foo'],
                'bar' => ['bar' => 'bar'],
            ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertInstanceOf(SomeFooObject::class, $result->objects['foo']);
        self::assertInstanceOf(SomeBarObject::class, $result->objects['bar']);
    }

    public function test_objects_sharing_one_property_are_resolved_correctly(): void
    {
        try {
            $result = $this->mapperBuilder->mapper()->map(UnionOfFooAndBarAndFoo::class, [
                ['foo' => 'foo'],
                ['foo' => 'foo', 'bar' => 'bar'],
            ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertInstanceOf(SomeFooObject::class, $result->objects[0]);
        self::assertInstanceOf(SomeFooAndBarObject::class, $result->objects[1]);
    }

    /**
     *
     * @dataProvider mapping_error_when_cannot_resolve_union_data_provider
     *
     * @param class-string $className
     * @param mixed[] $source
     */
    public function test_mapping_error_when_cannot_resolve_union(string $className, array $source): void
    {
        try {
            $this->mapperBuilder->mapper()->map($className, $source);

            self::fail('No mapping error when one was expected');
        } catch (MappingError $exception) {
            $error = $exception->node()->children()['objects']->children()[0]->messages()[0];

            self::assertSame('1641406600', $error->code());
        }
    }

    public function mapping_error_when_cannot_resolve_union_data_provider(): iterable
    {
        yield [
            'className' => UnionOfFooAndBar::class,
            'source' => [['foo' => 'foo', 'bar' => 'bar']],
        ];
        yield [
            'className' => UnionOfFooAndBarAndFiz::class,
            'source' => [['foo' => 'foo', 'bar' => 'bar', 'fiz' => 'fiz']],
        ];
        yield [
            'className' => UnionOfFooAndAnotherFoo::class,
            'source' => [['foo' => 'foo']],
        ];
    }
}

final class UnionOfFooAndBar
{
    /** @var array<SomeFooObject|SomeBarObject> */
    public array $objects;
}

final class UnionOfFooAndAnotherFoo
{
    /** @var array<SomeFooObject|SomeOtherFooObject> */
    public array $objects;
}

final class UnionOfFooAndBarAndFoo
{
    /** @var array<SomeFooAndBarObject|SomeFooObject> */
    public array $objects;
}

final class UnionOfFooAndBarAndFiz
{
    /** @var array<SomeFooObject|SomeBarAndFizObject> */
    public array $objects;
}

final class SomeFooObject
{
    public string $foo;
}

final class SomeOtherFooObject
{
    public string $foo;
}

final class SomeBarObject
{
    public string $bar;
}

final class SomeFooAndBarObject
{
    public string $foo;

    public string $bar;
}

final class SomeBarAndFizObject
{
    public string $bar;

    public string $fiz;
}
