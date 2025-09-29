<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Other;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\TestWith;
use stdClass;

final class UndefinedValuesMappingTest extends IntegrationTestCase
{
    #[TestWith(['type' => 'array{foo: string, bar: array<string>}', 'value' => ['foo' => 'foo'], 'expected' => ['foo' => 'foo', 'bar' => []]])]
    #[TestWith(['type' => 'array{foo: string, bar: array<string>}', 'value' => ['foo' => 'foo', 'bar' => null], 'expected' => ['foo' => 'foo', 'bar' => []]])]
    #[TestWith(['type' => 'array{foo: string, bar: list<string>}', 'value' => ['foo' => 'foo'], 'expected' => ['foo' => 'foo', 'bar' => []]])]
    #[TestWith(['type' => 'array{foo: string, bar: list<string>}', 'value' => ['foo' => 'foo', 'bar' => null], 'expected' => ['foo' => 'foo', 'bar' => []]])]
    public function test_array_types_can_accept_undefined_values(string $type, mixed $value, mixed $expected): void
    {
        try {
            $result = $this
                ->mapperBuilder()
                ->allowUndefinedValues()
                ->mapper()
                ->map($type, $value);

            self::assertSame($expected, $result);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }
    }

    public function test_null_value_for_class_fills_it_with_empty_array(): void
    {
        try {
            $this
                ->mapperBuilder()
                ->allowUndefinedValues()
                ->mapper()
                ->map(stdClass::class, null);

            self::expectNotToPerformAssertions();
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }
    }

    public function test_null_value_for_interface_with_no_properties_needed_fills_it_with_empty_array(): void
    {
        try {
            $result = $this->mapperBuilder()
                ->infer(SomeInterfaceForClassWithNoProperties::class, fn () => SomeClassWithNoProperties::class)
                ->allowUndefinedValues()
                ->mapper()
                ->map(SomeInterfaceForClassWithNoProperties::class, null);

            self::assertInstanceOf(SomeClassWithNoProperties::class, $result);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }
    }

    public function test_missing_value_for_nullable_property_fills_it_with_null(): void
    {
        $class = new class () {
            public string $foo;

            public ?string $bar;
        };

        try {
            $result = $this->mapperBuilder()
                ->infer(SomeInterfaceForClassWithNoProperties::class, fn () => SomeClassWithNoProperties::class)
                ->allowUndefinedValues()
                ->mapper()
                ->map($class::class, ['foo' => 'foo']);

            self::assertSame('foo', $result->foo);
            self::assertSame(null, $result->bar);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }
    }

    public function test_missing_value_for_nullable_shaped_array_element_fills_it_with_null(): void
    {
        try {
            $result = $this->mapperBuilder()
                ->infer(SomeInterfaceForClassWithNoProperties::class, fn () => SomeClassWithNoProperties::class)
                ->allowUndefinedValues()
                ->mapper()
                ->map(
                    'array{foo: string, bar: ?string}',
                    ['foo' => 'foo'],
                );

            self::assertSame('foo', $result['foo']);
            self::assertSame(null, $result['bar']);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }
    }

    public function test_non_empty_list_does_not_accept_null_when_undefined_values_are_allowed(): void
    {
        try {
            $this->mapperBuilder()
                ->allowUndefinedValues()
                ->mapper()
                ->map('array{values: non-empty-list<string>}', []);
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, [
                'values' => '[value_is_empty_list] Cannot be empty and must be filled with a value matching `non-empty-list<string>`.',
            ]);
        }
    }

    public function test_array_type_does_not_accept_null_when_undefined_values_are_not_allowed(): void
    {
        try {
            $this
                ->mapperBuilder()
                ->mapper()
                ->map('array<string>', null);
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, [
                '*root*' => '[value_is_not_iterable] Cannot be empty and must be filled with a value matching `array<string>`.',
            ]);
        }
    }

    public function test_list_type_does_not_accept_null_when_undefined_values_are_not_allowed(): void
    {
        try {
            $this
                ->mapperBuilder()
                ->mapper()
                ->map('list<string>', null);
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, [
                '*root*' => '[value_is_not_iterable] Cannot be empty and must be filled with a value matching `list<string>`.',
            ]);
        }
    }
}

interface SomeInterfaceForClassWithNoProperties {}

final class SomeClassWithNoProperties implements SomeInterfaceForClassWithNoProperties {}
