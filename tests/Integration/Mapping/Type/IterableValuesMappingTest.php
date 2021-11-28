<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Type;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Integration\IntegrationTest;
use CuyZ\Valinor\Tests\Integration\Mapping\Fixture\SimpleObject;
use CuyZ\Valinor\Tests\Integration\Mapping\Fixture\SimpleObject as SimpleObjectAlias;

final class IterableValuesMappingTest extends IntegrationTest
{
    public function test_values_are_mapped_properly(): void
    {
        $source = [
            'booleans' => [true, false, true],
            'floats' => [42.404, 404.42],
            'integers' => [42, 404, 1337],
            'strings' => ['foo', 'bar', 'baz'],
            'iterableWithDefaultKeyType' => [42 => 'foo', 'some-key' => 'bar'],
            'iterableWithIntegerKeyType' => [1337 => 'foo', 42.0 => 'bar', '404' => 'baz'],
            'iterableWithStringKeyType' => [1337 => 'foo', 42.0 => 'bar', 'some-key' => 'baz'],
            'objects' => [
                'foo' => ['value' => 'foo'],
                'bar' => ['value' => 'bar'],
                'baz' => ['value' => 'baz'],
            ],
            'objectsWithAlias' => [
                'foo' => ['value' => 'foo'],
                'bar' => ['value' => 'bar'],
                'baz' => ['value' => 'baz'],
            ],
        ];

        foreach ([IterableValues::class, IterableValuesWithConstructor::class] as $class) {
            try {
                $result = $this->mapperBuilder->mapper()->map($class, $source);
            } catch (MappingError $error) {
                $this->mappingFail($error);
            }

            /** @var SimpleObject[] $objects */
            $objects = $result->objects;

            /** @var SimpleObjectAlias[] $objectsWithAlias */
            $objectsWithAlias = $result->objectsWithAlias;

            self::assertSame($source['booleans'], $result->booleans);
            self::assertSame($source['floats'], $result->floats);
            self::assertSame($source['integers'], $result->integers);
            self::assertSame($source['strings'], $result->strings);
            self::assertSame($source['iterableWithDefaultKeyType'], $result->iterableWithDefaultKeyType);
            self::assertSame($source['iterableWithIntegerKeyType'], $result->iterableWithIntegerKeyType);
            self::assertSame($source['iterableWithStringKeyType'], $result->iterableWithStringKeyType);
            self::assertSame('foo', $objects['foo']->value);
            self::assertSame('bar', $objects['bar']->value);
            self::assertSame('baz', $objects['baz']->value);
            self::assertSame('foo', $objectsWithAlias['foo']->value);
            self::assertSame('bar', $objectsWithAlias['bar']->value);
            self::assertSame('baz', $objectsWithAlias['baz']->value);
        }
    }
}

class IterableValues
{
    /** @var iterable<bool> */
    public iterable $booleans;

    /** @var iterable<float> */
    public iterable $floats;

    /** @var iterable<int> */
    public iterable $integers;

    /** @var iterable<string> */
    public iterable $strings;

    /** @var iterable<array-key, string> */
    public iterable $iterableWithDefaultKeyType;

    /** @var iterable<int, string> */
    public iterable $iterableWithIntegerKeyType;

    /** @var iterable<string, string> */
    public iterable $iterableWithStringKeyType;

    /** @var iterable<SimpleObject> */
    public iterable $objects;

    /** @var iterable<SimpleObjectAlias> */
    public iterable $objectsWithAlias;
}

class IterableValuesWithConstructor extends IterableValues
{
    /**
     * @param iterable<bool> $booleans
     * @param iterable<float> $floats
     * @param iterable<int> $integers
     * @param iterable<string> $strings
     * @param iterable<array-key, string> $iterableWithDefaultKeyType
     * @param iterable<int, string> $iterableWithIntegerKeyType
     * @param iterable<string, string> $iterableWithStringKeyType
     * @param iterable<SimpleObject> $objects
     * @param iterable<SimpleObjectAlias> $objectsWithAlias
     */
    public function __construct(
        iterable $booleans,
        iterable $floats,
        iterable $integers,
        iterable $strings,
        iterable $iterableWithDefaultKeyType,
        iterable $iterableWithIntegerKeyType,
        iterable $iterableWithStringKeyType,
        iterable $objects,
        iterable $objectsWithAlias
    ) {
        $this->booleans = $booleans;
        $this->floats = $floats;
        $this->integers = $integers;
        $this->strings = $strings;
        $this->iterableWithDefaultKeyType = $iterableWithDefaultKeyType;
        $this->iterableWithIntegerKeyType = $iterableWithIntegerKeyType;
        $this->iterableWithStringKeyType = $iterableWithStringKeyType;
        $this->objects = $objects;
        $this->objectsWithAlias = $objectsWithAlias;
    }
}
