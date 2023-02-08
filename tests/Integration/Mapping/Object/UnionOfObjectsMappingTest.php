<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Object;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Tests\Integration\IntegrationTest;

final class UnionOfObjectsMappingTest extends IntegrationTest
{
    public function test_objects_sharing_one_property_are_resolved_correctly(): void
    {
        try {
            $result = (new MapperBuilder())
                // PHP8.1 first-class callable syntax
                ->registerConstructor([SomeFooAndBarObject::class, 'constructorA'])
                ->registerConstructor([SomeFooAndBarObject::class, 'constructorB'])
                ->mapper()
                ->map(UnionOfFooAndBarAndFoo::class, [
                'foo',
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
            (new MapperBuilder())->mapper()->map($className, $source);

            self::fail('No mapping error when one was expected');
        } catch (MappingError $exception) {
            $error = $exception->node()->children()[0]->messages()[0];

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

final class NativeUnionOfObjects
{
    public SomeFooObject|SomeBarObject $object;
}

// PHP8.1 Readonly properties
final class UnionOfFooAndBar
{
    /** @var array<SomeFooObject|SomeBarObject> */
    public array $objects;
}

// PHP8.1 Readonly properties
final class UnionOfFooAndAnotherFoo
{
    /** @var array<SomeFooObject|SomeOtherFooObject> */
    public array $objects;
}

// PHP8.1 Readonly properties
final class UnionOfFooAndBarAndFoo
{
    /** @var array<SomeFooAndBarObject|SomeFooObject> */
    public array $objects;
}

// PHP8.1 Readonly properties
final class SomeFooObject
{
    public string $foo;
}

// PHP8.1 Readonly properties
final class SomeOtherFooObject
{
    public string $foo;
}

// PHP8.1 Readonly properties
final class SomeBarObject
{
    public string $bar;
}

// PHP8.1 Readonly properties
final class SomeFooAndBarObject
{
    public string $foo;

    public string $bar;

    public string $baz;

    private function __construct(string $foo, string $bar, string $baz)
    {
        $this->foo = $foo;
        $this->bar = $bar;
        $this->baz = $baz;
    }

    public static function constructorA(string $foo, string $bar, string $baz): self
    {
        return new self($foo, $bar, $baz);
    }

    public static function constructorB(string $foo, string $bar): self
    {
        return new self($foo, $bar, 'default baz');
    }
}
