<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Object;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Tests\Integration\IntegrationTest;
use CuyZ\Valinor\Tests\Integration\Mapping\Fixture\SimpleObject;
use stdClass;

final class ObjectValuesMappingTest extends IntegrationTest
{
    public function test_values_are_mapped_properly(): void
    {
        $source = [
            'string' => 'foo',
            'object' => 'foo',
        ];

        foreach ([ObjectValues::class, ObjectValuesWithConstructor::class] as $class) {
            try {
                $result = (new MapperBuilder())->mapper()->map($class, $source);
            } catch (MappingError $error) {
                $this->mappingFail($error);
            }

            self::assertSame('foo', $result->object->value);
        }
    }

    public function test_invalid_iterable_source_throws_exception(): void
    {
        $source = 'foo';

        foreach ([ObjectValues::class, ObjectValuesWithConstructor::class] as $class) {
            try {
                (new MapperBuilder())->mapper()->map($class, $source);
            } catch (MappingError $exception) {
                $error = $exception->node()->messages()[0];

                self::assertSame('1632903281', $error->code());
                self::assertSame("Value 'foo' does not match type `array{object: ?, string: string}`.", (string)$error);
            }
        }
    }

    public function test_superfluous_values_throws_exception(): void
    {
        try {
            (new MapperBuilder())->mapper()->map(ObjectWithTwoProperties::class, [
                'stringA' => 'fooA',
                'stringB' => 'fooB',
                'unexpectedValueA' => 'foo',
                'unexpectedValueB' => 'bar',
                42 => 'baz',
            ]);
        } catch (MappingError $exception) {
            $error = $exception->node()->messages()[0];

            self::assertSame('1655149208', $error->code());
            self::assertSame('Unexpected key(s) `unexpectedValueA`, `unexpectedValueB`, `42`, expected `stringA`, `stringB`.', (string)$error);
        }
    }

    public function test_object_with_no_argument_build_with_non_array_source_throws_exception(): void
    {
        try {
            (new MapperBuilder())->mapper()->map(stdClass::class, 'foo');
        } catch (MappingError $exception) {
            $error = $exception->node()->messages()[0];

            self::assertSame('1632903281', $error->code());
            self::assertSame("Value 'foo' does not match type array.", (string)$error);
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
