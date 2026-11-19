<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Object;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Fixture\Object\ReadonlyPropertyWithDefaultValue\ClassWithReadonlyPropertyWithDefaultValue;
use CuyZ\Valinor\Tests\Fixture\Object\ReadonlyPropertyWithDefaultValue\ReadonlyClassWithPropertyWithDefaultValue;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\RequiresPhp;

final class ReadonlyMappingTest extends IntegrationTestCase
{
    public function test_single_property_and_constructor_parameter_are_mapped_properly(): void
    {
        $class = new class () {
            public readonly string $value; // @phpstan-ignore-line
        };

        try {
            $object = $this->mapperBuilder()->mapper()->map($class::class, 'foo');
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $object->value);
    }

    #[RequiresPhp('>=8.6')]
    public function test_readonly_property_with_default_value_is_not_mapped(): void
    {
        try {
            $object = $this->mapperBuilder()
                ->mapper()
                ->map(ClassWithReadonlyPropertyWithDefaultValue::class, [
                    'foo' => 'foo',
                    'bar' => 'bar',
                ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('default value', $object->readonlyWithDefault);
        self::assertSame('foo', $object->foo);
        self::assertSame('bar', $object->bar);
    }

    #[RequiresPhp('>=8.6')]
    public function test_property_with_default_value_in_readonly_class_is_not_mapped(): void
    {
        try {
            $object = $this->mapperBuilder()
                ->mapper()
                ->map(ReadonlyClassWithPropertyWithDefaultValue::class, [
                    'foo' => 'foo',
                    'bar' => 'bar',
                ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('default value', $object->withDefault);
        self::assertSame('foo', $object->foo);
        self::assertSame('bar', $object->bar);
    }

    #[RequiresPhp('>=8.6')]
    public function test_value_given_for_readonly_property_with_default_value_is_unexpected(): void
    {
        try {
            $this->mapperBuilder()
                ->mapper()
                ->map(ClassWithReadonlyPropertyWithDefaultValue::class, [
                    'readonlyWithDefault' => 'some value',
                    'foo' => 'foo',
                    'bar' => 'bar',
                ]);

            self::fail('No mapping error when one was expected');
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, [
                'readonlyWithDefault' => '[unexpected_key] Unexpected key `readonlyWithDefault`.',
            ]);
        }
    }
}
