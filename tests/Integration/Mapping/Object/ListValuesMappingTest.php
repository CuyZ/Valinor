<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Object;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Tree\Exception\InvalidIterableKeyType;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;
use CuyZ\Valinor\Tests\Integration\Mapping\Fixture\SimpleObject;
use CuyZ\Valinor\Tests\Integration\Mapping\Fixture\SimpleObject as SimpleObjectAlias;
use stdClass;

final class ListValuesMappingTest extends IntegrationTestCase
{
    public function test_values_are_mapped_properly(): void
    {
        $source = [
            'booleans' => [true, false, true],
            'floats' => [42.404, 404.42],
            'integers' => [42, 404, 1337],
            'strings' => ['foo', 'bar', 'baz'],
            'objects' => ['foo', 'bar', 'baz'],
            'objectsWithAlias' => ['foo', 'bar', 'baz'],
            'listOfStrings' => ['foo', 'bar', 'baz',],
            'nonEmptyListOfStrings' => ['foo', 'bar', 'baz'],
        ];

        foreach ([ListValues::class, ListValuesWithConstructor::class] as $class) {
            try {
                $result = $this->mapperBuilder()->mapper()->map($class, $source);
            } catch (MappingError $error) {
                $this->mappingFail($error);
            }

            self::assertSame($source['booleans'], $result->booleans);
            self::assertSame($source['floats'], $result->floats);
            self::assertSame($source['integers'], $result->integers);
            self::assertSame($source['strings'], $result->strings);
            self::assertSame('foo', $result->objects[0]->value);
            self::assertSame('bar', $result->objects[1]->value);
            self::assertSame('baz', $result->objects[2]->value);
            self::assertSame('foo', $result->objectsWithAlias[0]->value);
            self::assertSame('bar', $result->objectsWithAlias[1]->value);
            self::assertSame('baz', $result->objectsWithAlias[2]->value);
            self::assertSame($source['listOfStrings'], $result->listOfStrings);
            self::assertSame($source['nonEmptyListOfStrings'], $result->nonEmptyListOfStrings);
        }
    }

    public function test_empty_list_in_non_empty_list_throws_exception(): void
    {
        try {
            $this->mapperBuilder()->mapper()->map('non-empty-list<string>', []);
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, [
                '*root*' => "[1630678334] Value array (empty) does not match type `non-empty-list<string>`.",
            ]);
        }
    }

    public function test_map_array_with_non_sequential_keys_to_list_throws_exception(): void
    {
        try {
            $this->mapperBuilder()->mapper()->map('list<string>', [
                0 => 'foo',
                2 => 'bar',
            ]);
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, [
                '2' => '[1654273010] Invalid sequential key 2, expected 1.',
            ]);
        }
    }

    public function test_value_with_invalid_type_throws_exception(): void
    {
        try {
            $this->mapperBuilder()->mapper()->map('list<int>', ['foo']);
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, [
                '0' => "[unknown] Value 'foo' is not a valid integer.",
            ]);
        }
    }

    public function test_invalid_list_key_type_throws_exception(): void
    {
        $this->expectException(InvalidIterableKeyType::class);
        $this->expectExceptionCode(1737104770);
        $this->expectExceptionMessage('Invalid key of type `stdClass` at path `*root*`, only integers and strings are allowed.');

        $this->mapperBuilder()->mapper()->map(
            'list<string>',
            (function () {
                yield new stdClass() => 'foo';
            })(),
        );
    }
}

class ListValues
{
    /** @var list<bool> */
    public array $booleans;

    /** @var list<float> */
    public array $floats;

    /** @var list<int> */
    public array $integers;

    /** @var list<string> */
    public array $strings;

    /** @var list<SimpleObject> */
    public array $objects;

    /** @var list<SimpleObjectAlias> */
    public array $objectsWithAlias;

    /** @var list<string> */
    public array $listOfStrings;

    /** @var non-empty-list<string> */
    public array $nonEmptyListOfStrings = ['foo'];
}

class ListValuesWithConstructor extends ListValues
{
    /**
     * @param list<bool> $booleans
     * @param list<float> $floats
     * @param list<int> $integers
     * @param list<string> $strings
     * @param list<SimpleObject> $objects
     * @param list<SimpleObjectAlias> $objectsWithAlias
     * @param list<string> $listOfStrings
     * @param non-empty-list<string> $nonEmptyListOfStrings
     */
    public function __construct(
        array $booleans,
        array $floats,
        array $integers,
        array $strings,
        array $objects,
        array $objectsWithAlias,
        array $listOfStrings,
        array $nonEmptyListOfStrings
    ) {
        $this->booleans = $booleans;
        $this->floats = $floats;
        $this->integers = $integers;
        $this->strings = $strings;
        $this->objects = $objects;
        $this->objectsWithAlias = $objectsWithAlias;
        $this->listOfStrings = $listOfStrings;
        $this->nonEmptyListOfStrings = $nonEmptyListOfStrings;
    }
}
