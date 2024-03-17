<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Object;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;

final class UnionOfObjectsMappingTest extends IntegrationTestCase
{
    public function test_objects_sharing_one_property_are_resolved_correctly(): void
    {
        try {
            $result = $this->mapperBuilder()
                ->registerConstructor(SomeFooAndBarObject::constructorA(...))
                ->registerConstructor(SomeFooAndBarObject::constructorB(...))
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

    public function test_mapping_to_union_of_null_and_objects_can_infer_object(): void
    {
        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map(
                    'null|' . SomeObjectWithFooAndBar::class . '|' . SomeObjectWithBazAndFiz::class,
                    [
                        'baz' => 'baz',
                        'fiz' => 'fiz',
                    ]
                );
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertInstanceOf(SomeObjectWithBazAndFiz::class, $result);
        self::assertSame('baz', $result->baz);
        self::assertSame('fiz', $result->fiz);
    }

    public static function mapping_error_when_cannot_resolve_union_data_provider(): iterable
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

final class SomeObjectWithFooAndBar
{
    public string $foo;

    public string $bar;
}

final class SomeObjectWithBazAndFiz
{
    public string $baz;

    public string $fiz;
}
