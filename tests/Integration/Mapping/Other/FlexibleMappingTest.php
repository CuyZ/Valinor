<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Other;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Tests\Fixture\Enum\BackedIntegerEnum;
use CuyZ\Valinor\Tests\Fixture\Enum\BackedStringEnum;
use CuyZ\Valinor\Tests\Fixture\Object\StringableObject;
use CuyZ\Valinor\Tests\Integration\IntegrationTest;
use DateTime;
use stdClass;

use function get_class;

final class FlexibleMappingTest extends IntegrationTest
{
    public function test_array_of_scalars_is_mapped_properly(): void
    {
        $source = ['foo', 42, 1337.404];

        try {
            $result = (new MapperBuilder())->flexible()->mapper()->map('string[]', $source);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(['foo', '42', '1337.404'], $result);
    }

    public function test_shaped_array_is_mapped_correctly(): void
    {
        $source = [
            'foo',
            'foo' => '42',
        ];

        try {
            $result = (new MapperBuilder())->flexible()->mapper()->map('array{string, foo: int, bar?: float}', $source);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(['foo', 'foo' => 42], $result);
    }

    /**
     * @requires PHP >= 8.1
     */
    public function test_string_enum_is_cast_correctly(): void
    {
        try {
            $result = (new MapperBuilder())->flexible()->mapper()->map(BackedStringEnum::class, new StringableObject('foo'));
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(BackedStringEnum::FOO, $result);
    }

    /**
     * @requires PHP >= 8.1
     */
    public function test_integer_enum_is_cast_correctly(): void
    {
        try {
            $result = (new MapperBuilder())->flexible()->mapper()->map(BackedIntegerEnum::class, '42');
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(BackedIntegerEnum::FOO, $result);
    }

    public function test_superfluous_shaped_array_values_are_mapped_properly_in_flexible_mode(): void
    {
        $source = [
            'foo' => 'foo',
            'bar' => 42,
            'fiz' => 1337.404,
        ];

        foreach (['array{foo: string, bar: int}', 'array{bar: int, fiz:float}'] as $signature) {
            try {
                $result = (new MapperBuilder())->flexible()->mapper()->map($signature, $source);
            } catch (MappingError $error) {
                $this->mappingFail($error);
            }

            self::assertSame(42, $result['bar']);
        }
    }

    public function test_source_matching_two_unions_maps_the_one_with_most_arguments(): void
    {
        try {
            $result = (new MapperBuilder())->flexible()->mapper()->map(UnionOfBarAndFizAndFoo::class, [
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

    public function test_can_map_to_mixed_type_in_flexible_mode(): void
    {
        try {
            $result = (new MapperBuilder())->flexible()->mapper()->map('mixed[]', [
                'foo' => 'foo',
                'bar' => 'bar',
            ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(['foo' => 'foo', 'bar' => 'bar'], $result);
    }

    public function test_can_map_to_undefined_object_type_in_flexible_mode(): void
    {
        $source = [new stdClass(), new DateTime()];

        try {
            $result = (new MapperBuilder())->flexible()->mapper()->map('object[]', $source);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame($source, $result);
    }

    public function test_missing_value_for_array_fills_it_with_empty_array(): void
    {
        try {
            $result = (new MapperBuilder())->flexible()->mapper()->map(
                'array{foo: string, bar: array<string>}',
                ['foo' => 'foo']
            );
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $result['foo']);
        self::assertSame([], $result['bar']);
    }

    public function test_null_value_for_array_fills_it_with_empty_array(): void
    {
        try {
            $result = (new MapperBuilder())->flexible()->mapper()->map(
                'array{foo: string, bar: array<string>}',
                [
                    'foo' => 'foo',
                    'bar' => null
                ]
            );
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $result['foo']);
        self::assertSame([], $result['bar']);
    }

    public function test_missing_value_for_list_fills_it_with_empty_array(): void
    {
        try {
            $result = (new MapperBuilder())->flexible()->mapper()->map(
                'array{foo: string, bar: list<string>}',
                ['foo' => 'foo']
            );
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $result['foo']);
        self::assertSame([], $result['bar']);
    }

    public function test_null_value_for_list_fills_it_with_empty_array(): void
    {
        try {
            $result = (new MapperBuilder())->flexible()->mapper()->map(
                'array{foo: string, bar: list<string>}',
                [
                    'foo' => 'foo',
                    'bar' => null
                ]
            );
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $result['foo']);
        self::assertSame([], $result['bar']);
    }

    public function test_missing_value_for_nullable_property_fills_it_with_null(): void
    {
        $class = new class () {
            public string $foo;

            /** @noRector \Rector\Php74\Rector\Property\RestoreDefaultNullToNullableTypePropertyRector */
            public ?string $bar;
        };

        try {
            $result = (new MapperBuilder())->flexible()->mapper()->map(
                get_class($class),
                ['foo' => 'foo']
            );
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $result->foo);
        self::assertSame(null, $result->bar);
    }

    public function test_missing_value_for_nullable_shaped_array_element_fills_it_with_null(): void
    {
        try {
            $result = (new MapperBuilder())->flexible()->mapper()->map(
                'array{foo: string, bar: ?string}',
                ['foo' => 'foo']
            );
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $result['foo']);
        self::assertSame(null, $result['bar']);
    }
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
final class SomeBarAndFizObject
{
    public string $bar;

    public string $fiz;
}
