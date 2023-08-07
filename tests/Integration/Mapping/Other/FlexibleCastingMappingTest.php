<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Other;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\TreeMapper;
use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Tests\Fixture\Enum\BackedIntegerEnum;
use CuyZ\Valinor\Tests\Fixture\Enum\BackedStringEnum;
use CuyZ\Valinor\Tests\Fixture\Object\StringableObject;
use CuyZ\Valinor\Tests\Integration\IntegrationTest;
use stdClass;

final class FlexibleCastingMappingTest extends IntegrationTest
{
    private TreeMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mapper = (new MapperBuilder())->enableFlexibleCasting()->mapper();
    }

    public function test_array_of_scalars_is_mapped_properly(): void
    {
        $source = ['foo', 42, 1337.404];

        try {
            $result = $this->mapper->map('string[]', $source);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(['foo', '42', '1337.404'], $result);
    }

    public function test_shaped_array_is_mapped_correctly(): void
    {
        try {
            $result = $this->mapper->map(
                'array{string, foo: int, bar?: float}',
                [
                    'foo',
                    'foo' => '42',
                ]
            );
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
            $result = $this->mapper->map(BackedStringEnum::class, new StringableObject('foo'));
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
            $result = $this->mapper->map(BackedIntegerEnum::class, '42');
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(BackedIntegerEnum::FOO, $result);
    }

    public function test_list_filled_with_associative_array_is_converted_to_list(): void
    {
        try {
            $result = $this->mapper->map(
                'list<string>',
                [
                    'foo' => 'foo',
                    'bar' => 'bar',
                ]
            );
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(['foo', 'bar'], $result);
    }

    public function test_null_value_for_class_fills_it_with_empty_array(): void
    {
        try {
            $this->mapper->map(stdClass::class, null);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::expectNotToPerformAssertions();
    }

    public function test_null_value_for_interface_with_no_properties_needed_fills_it_with_empty_array(): void
    {
        try {
            $result = (new MapperBuilder())
                ->infer(SomeInterfaceForClassWithNoProperties::class, fn () => SomeClassWithNoProperties::class)
                ->enableFlexibleCasting()
                ->mapper()
                ->map(SomeInterfaceForClassWithNoProperties::class, null);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertInstanceOf(SomeClassWithNoProperties::class, $result);
    }

    public function test_interface_is_inferred_and_mapped_properly_in_flexible_casting_mode(): void
    {
        try {
            $result = (new MapperBuilder())
                ->infer(SomeInterfaceForClassWithProperties::class, fn () => SomeClassWithProperties::class)
                ->enableFlexibleCasting()
                ->mapper()
                ->map(SomeInterfaceForClassWithProperties::class, [
                    'foo' => 'foo',
                    'bar' => 'bar',
                ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertInstanceOf(SomeClassWithProperties::class, $result);
        self::assertSame('foo', $result->foo);
        self::assertSame('bar', $result->bar);
    }

    public function test_missing_value_for_array_fills_it_with_empty_array(): void
    {
        try {
            $result = $this->mapper->map(
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
            $result = $this->mapper->map(
                'array{foo: string, bar: array<string>}',
                [
                    'foo' => 'foo',
                    'bar' => null,
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
            $result = $this->mapper->map(
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
            $result = $this->mapper->map(
                'array{foo: string, bar: list<string>}',
                [
                    'foo' => 'foo',
                    'bar' => null,
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

            public ?string $bar;
        };

        try {
            $result = $this->mapper->map(
                $class::class,
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
            $result = $this->mapper->map(
                'array{foo: string, bar: ?string}',
                ['foo' => 'foo']
            );
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $result['foo']);
        self::assertSame(null, $result['bar']);
    }

    public function test_filled_source_value_is_casted_when_union_contains_three_types_including_null(): void
    {
        try {
            $result = $this->mapper->map('null|int|string', new StringableObject('foo'));
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $result);
    }

    public function test_source_value_is_casted_when_other_type_cannot_be_casted(): void
    {
        try {
            $result = $this->mapper->map('string[]|string', new StringableObject('foo'));
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $result);
    }
}

interface SomeInterfaceForClassWithNoProperties {}

final class SomeClassWithNoProperties implements SomeInterfaceForClassWithNoProperties {}

interface SomeInterfaceForClassWithProperties {}

final class SomeClassWithProperties implements SomeInterfaceForClassWithProperties
{
    public string $foo;

    public string $bar;
}
