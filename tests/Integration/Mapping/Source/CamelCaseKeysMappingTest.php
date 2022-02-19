<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Source;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Source\Modifier\CamelCaseKeys;
use CuyZ\Valinor\Tests\Integration\IntegrationTest;

final class CamelCaseKeysMappingTest extends IntegrationTest
{
    public function test_underscore_key_is_modified_to_camel_case(): void
    {
        try {
            $object = $this->mapperBuilder->mapper()->map(
                SomeClassWithCamelCaseProperty::class,
                new CamelCaseKeys(['some_value' => 'foo'])
            );
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $object->someValue);
    }

    public function test_dash_key_is_modified_to_camel_case(): void
    {
        try {
            $object = $this->mapperBuilder->mapper()->map(
                SomeClassWithCamelCaseProperty::class,
                new CamelCaseKeys(['some-value' => 'foo'])
            );
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $object->someValue);
    }

    public function test_spaced_key_is_modified_to_camel_case(): void
    {
        try {
            $object = $this->mapperBuilder->mapper()->map(
                SomeClassWithCamelCaseProperty::class,
                new CamelCaseKeys(['some value' => 'foo'])
            );
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $object->someValue);
    }

    public function test_nested_camel_case_keys_are_modified(): void
    {
        try {
            $object = $this->mapperBuilder->mapper()->map(
                SomeClassWithNestedProperty::class,
                new CamelCaseKeys([
                    'some_nested_value' => ['some_value' => 'foo'],
                ])
            );
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $object->someNestedValue->someValue);
    }

    public function test_existing_camel_case_key_is_not_overridden(): void
    {
        try {
            $object = $this->mapperBuilder->mapper()->map(
                SomeClassWithCamelCaseProperty::class,
                new CamelCaseKeys([
                    'someValue' => 'bar',
                    'some_value' => 'foo',
                ])
            );
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('bar', $object->someValue);
    }

    public function test_multiple_camel_case_keys_are_modified(): void
    {
        try {
            $object = $this->mapperBuilder->mapper()->map(
                SomeClassWithCamelCaseProperty::class,
                new CamelCaseKeys([
                    'some_value' => 'foo',
                    'someValue' => 'bar',
                    'some_other_value' => 'buz',
                ])
            );
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $object->someValue);
        self::assertSame('buz', $object->someOtherValue);
    }
}

final class SomeClassWithCamelCaseProperty
{
    public string $someValue;

    public string $someOtherValue = 'fiz';
}

final class SomeClassWithNestedProperty
{
    public SomeClassWithCamelCaseProperty $someNestedValue;
}
