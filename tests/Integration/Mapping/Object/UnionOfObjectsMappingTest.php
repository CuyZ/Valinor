<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Object;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\MapperBuilder;
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
            $resultFoo = (new MapperBuilder())->mapper()->map(NativeUnionOfObjects::class, [
                'foo' => 'foo',
            ]);
            $resultBar = (new MapperBuilder())->mapper()->map(NativeUnionOfObjects::class, [
                'bar' => 'bar',
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
            $result = (new MapperBuilder())->mapper()->map(UnionOfFooAndBar::class, [
                'foo' => ['foo' => 'foo'],
                'bar' => ['bar' => 'bar'],
            ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertInstanceOf(SomeFooObject::class, $result->objects['foo']);
        self::assertInstanceOf(SomeBarObject::class, $result->objects['bar']);
    }

    public function test_source_matching_two_unions_maps_the_one_with_most_arguments(): void
    {
        try {
            $result = (new MapperBuilder())->mapper()->map(UnionOfBarAndFizAndFoo::class, [
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

    public function test_objects_sharing_one_property_are_resolved_correctly(): void
    {
        try {
            $result = (new MapperBuilder())->mapper()->map(UnionOfFooAndBarAndFoo::class, [
                ['foo' => 'foo'],
                ['foo' => 'foo', 'bar' => 'bar'],
            ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertInstanceOf(SomeFooObject::class, $result->objects[0]);
        self::assertInstanceOf(SomeFooAndBarObject::class, $result->objects[1]);
    }

    public function test_one_failing_union_type_does_not_stop_union_inferring(): void
    {
        try {
            $result = (new MapperBuilder())
                // @PHP8.1 first-class callable syntax
                ->registerConstructor(
                    [SomeClassWithTwoIdenticalNamedConstructors::class, 'constructorA'],
                    [SomeClassWithTwoIdenticalNamedConstructors::class, 'constructorB'],
                )
                ->mapper()
                ->map(SomeClassWithOneFailingUnionType::class, [
                    'object' => ['foo' => 'foo'],
                ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $result->object->foo);
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
            (new MapperBuilder())->mapper()->map($className, $source);

            self::fail('No mapping error when one was expected');
        } catch (MappingError $exception) {
            $error = $exception->node()->children()['objects']->children()[0]->messages()[0];

            self::assertSame('1642787246', $error->code());
        }
    }

    public function mapping_error_when_cannot_resolve_union_data_provider(): iterable
    {
        yield [
            'className' => UnionOfFooAndBar::class,
            'source' => [['foo' => 'foo', 'bar' => 'bar']],
        ];
        yield [
            'className' => UnionOfFooAndAnotherFoo::class,
            'source' => [['foo' => 'foo']],
        ];
    }
}

// @PHP8.1 Readonly properties
final class UnionOfFooAndBar
{
    /** @var array<SomeFooObject|SomeBarObject> */
    public array $objects;
}

// @PHP8.1 Readonly properties
final class UnionOfFooAndAnotherFoo
{
    /** @var array<SomeFooObject|SomeOtherFooObject> */
    public array $objects;
}

// @PHP8.1 Readonly properties
final class UnionOfFooAndBarAndFoo
{
    /** @var array<SomeFooAndBarObject|SomeFooObject> */
    public array $objects;
}

// @PHP8.1 Readonly properties
final class UnionOfBarAndFizAndFoo
{
    /** @var array<SomeBarAndFizObject|SomeFooObject> */
    public array $objects;
}

// @PHP8.1 Readonly properties
final class SomeFooObject
{
    public string $foo;
}

// @PHP8.1 Readonly properties
final class SomeOtherFooObject
{
    public string $foo;
}

// @PHP8.1 Readonly properties
final class SomeBarObject
{
    public string $bar;
}

// @PHP8.1 Readonly properties
final class SomeFooAndBarObject
{
    public string $foo;

    public string $bar;
}

// @PHP8.1 Readonly properties
final class SomeBarAndFizObject
{
    public string $bar;

    public string $fiz;
}

final class SomeClassWithOneFailingUnionType
{
    // @PHP8.0 native union
    // @PHP8.0 Promoted property
    // @PHP8.1 Readonly property
    /** @var SomeClassWithTwoIdenticalNamedConstructors|SomeFooObject */
    public object $object;
}

final class SomeClassWithTwoIdenticalNamedConstructors
{
    public string $foo;

    // @PHP8.0 Promoted properties
    // @PHP8.1 Readonly properties
    public function __construct(string $foo)
    {
        $this->foo = $foo;
    }

    public static function constructorA(string $foo): self
    {
        return new self($foo);
    }

    public static function constructorB(string $foo): self
    {
        return new self($foo);
    }
}
