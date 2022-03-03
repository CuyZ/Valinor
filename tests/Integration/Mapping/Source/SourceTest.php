<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Source;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Source\Source;
use CuyZ\Valinor\Tests\Integration\IntegrationTest;
use IteratorAggregate;
use Traversable;

final class SourceTest extends IntegrationTest
{
    public function test_iterable_source(): void
    {
        try {
            $object = $this->mapperBuilder->mapper()->map(
                SomeClassWithSubProperty::class,
                Source::iterable(new SomeIterableClass())
            );
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $object->someNestedValue->someValue);
    }

    public function test_array_source(): void
    {
        try {
            $object = $this->mapperBuilder->mapper()->map(
                SomeClassWithSubProperty::class,
                Source::array(['someNestedValue' => ['someValue' => 'foo']])
            );
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $object->someNestedValue->someValue);
    }

    public function test_json_source(): void
    {
        try {
            $object = $this->mapperBuilder->mapper()->map(
                SomeClassWithSubProperty::class,
                Source::json('{"someNestedValue": {"someValue": "foo"}}')
            );
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $object->someNestedValue->someValue);
    }

    public function test_yaml_source(): void
    {
        try {
            $object = $this->mapperBuilder->mapper()->map(
                SomeClassWithSubProperty::class,
                Source::yaml("someNestedValue:\n someValue: foo")
            );
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $object->someNestedValue->someValue);
    }

    public function test_camel_case_keys(): void
    {
        try {
            $object = $this->mapperBuilder->mapper()->map(
                SomeClassWithSubProperty::class,
                Source::json('{"some nested value": {"some value": "foo"}}')
                    ->camelCaseKeys()
            );
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $object->someNestedValue->someValue);
    }

    public function test_path_mapping(): void
    {
        try {
            $object = $this->mapperBuilder->mapper()->map(
                SomeClassWithSubProperty::class,
                Source::json('{"A": {"B": "foo"}}')
                    ->map([
                        'A' => 'someNestedValue',
                        'A.B' => 'someValue',
                    ])
            );
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $object->someNestedValue->someValue);
    }

    public function test_camel_case_keys_then_path_mapping(): void
    {
        try {
            $object = $this->mapperBuilder->mapper()->map(
                SomeClassWithSubProperty::class,
                Source::json('{"level-one": {"level-two": "foo"}}')
                    ->camelCaseKeys()
                    ->map([
                        'levelOne' => 'someNestedValue',
                        'levelOne.levelTwo' => 'someValue',
                    ])
            );
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $object->someNestedValue->someValue);
    }

    public function test_path_mapping_then_camel_case_keys(): void
    {
        try {
            $object = $this->mapperBuilder->mapper()->map(
                SomeClassWithSubProperty::class,
                Source::json('{"level-one": {"level-two": "foo"}}')
                    ->map([
                        'level-one' => 'some-nested-value',
                        'level-one.level-two' => 'some-value',
                    ])
                    ->camelCaseKeys()
            );
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $object->someNestedValue->someValue);
    }
}

final class SomeClassWithSingleProperty
{
    public string $someValue;
}

final class SomeClassWithSubProperty
{
    public SomeClassWithSingleProperty $someNestedValue;
}

/**
 * @implements IteratorAggregate<mixed>
 */
final class SomeIterableClass implements IteratorAggregate
{
    public function getIterator(): Traversable
    {
        yield from ['someNestedValue' => ['someValue' => 'foo']];
    }
}
