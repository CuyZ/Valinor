<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Object;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Source\Source;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;
use CuyZ\Valinor\Tests\Integration\Mapping\Fixture\SimpleObject;
use stdClass;

final class ObjectValuesMappingTest extends IntegrationTestCase
{
    public function test_values_are_mapped_properly(): void
    {
        $source = [
            'string' => 'foo',
            'object' => 'foo',
        ];

        foreach ([ObjectValues::class, ObjectValuesWithConstructor::class] as $class) {
            try {
                $result = $this->mapperBuilder()->mapper()->map($class, $source);
            } catch (MappingError $error) {
                $this->mappingFail($error);
            }

            self::assertSame('foo', $result->object->value);
        }
    }

    public function test_interface_with_no_infer_is_mapped_when_an_object_implementing_this_interface_is_given(): void
    {
        $source = new SomeClassImplementingSomeInterface();

        $result = $this->mapperBuilder()->mapper()->map(SomeInterfaceWithOneImplementation::class, $source);

        self::assertSame($source, $result);
    }

    public function test_invalid_iterable_source_throws_exception(): void
    {
        $source = 'foo';

        foreach ([ObjectValues::class, ObjectValuesWithConstructor::class] as $class) {
            try {
                $this->mapperBuilder()->mapper()->map($class, $source);
            } catch (MappingError $exception) {
                self::assertMappingErrors($exception, [
                    '*root*' => "[value_is_not_iterable] Value 'foo' does not match `array{object: string|array{value: string}, string: string}`.",
                ]);
            }
        }
    }

    public function test_superfluous_values_throws_exception_and_keeps_nested_errors(): void
    {
        try {
            $this->mapperBuilder()->mapper()->map(ObjectWithTwoProperties::class, [
                'stringA' => 42,
                'stringB' => 'fooB',
                'unexpectedValueA' => 'foo',
                'unexpectedValueB' => 'bar',
                42 => 'baz',
            ]);
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, [
                '*root*' => "[unexpected_keys] Unexpected key(s) `unexpectedValueA`, `unexpectedValueB`, `42`, expected `stringA`, `stringB`.",
                'stringA' => '[invalid_string] Value 42 is not a valid string.'
            ]);
        }
    }

    public function test_superfluous_values_throws_exception_when_source_is_iterable_but_not_array(): void
    {
        try {
            $this->mapperBuilder()->mapper()->map(ObjectWithTwoProperties::class, Source::iterable([
                'stringA' => 'fooA',
                'stringB' => 'fooB',
                'unexpectedValue' => 'foo',
                42 => 'baz',
            ]));
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, [
                '*root*' => "[unexpected_keys] Unexpected key(s) `unexpectedValue`, `42`, expected `stringA`, `stringB`.",
            ]);
        }
    }

    public function test_object_with_no_argument_build_with_non_array_source_throws_exception(): void
    {
        try {
            $this->mapperBuilder()->mapper()->map(stdClass::class, 'foo');
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, [
                '*root*' => "[value_is_not_iterable] Value 'foo' does not match `array{}`.",
            ]);
        }
    }

    public function test_nested_error_path_is_correctly_flattened_when_using_single_argument(): void
    {
        $class = (new class () {
            public ObjectWithSingleShapedArrayProperty $subObject;
        })::class;

        try {
            $this->mapperBuilder()->mapper()->map(
                $class,
                ['first' => ['second' => 'foo']],
            );
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, [
                'first.second' => "[invalid_float] Value 'foo' is not a valid float.",
            ]);
        }
    }
}

class ObjectValues
{
    public SimpleObject $object;

    public string $string;
}

class ObjectValuesWithConstructor extends ObjectValues
{
    public function __construct(SimpleObject $object, string $string)
    {
        $this->object = $object;
        $this->string = $string;
    }
}

final class ObjectWithTwoProperties
{
    public string $stringA;

    public string $stringB;
}

interface SomeInterfaceWithOneImplementation {}

final class SomeClassImplementingSomeInterface implements SomeInterfaceWithOneImplementation {}

final class ObjectWithSingleShapedArrayProperty
{
    /** @var array{first: array{second: float}} */
    public array $value;
}
