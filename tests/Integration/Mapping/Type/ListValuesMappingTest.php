<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Type;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Tree\Exception\CannotCastToScalarValue;
use CuyZ\Valinor\Mapper\Tree\Exception\InvalidNodeValue;
use CuyZ\Valinor\Tests\Integration\IntegrationTest;
use CuyZ\Valinor\Tests\Integration\Mapping\Fixture\SimpleObject;
use CuyZ\Valinor\Tests\Integration\Mapping\Fixture\SimpleObject as SimpleObjectAlias;

use function array_values;

final class ListValuesMappingTest extends IntegrationTest
{
    public function test_values_are_mapped_properly(): void
    {
        $source = [
            'booleans' => [true, false, true],
            'floats' => [42.404, 404.42],
            'integers' => [42, 404, 1337],
            'strings' => ['foo', 'bar', 'baz'],
            'objects' => [
                ['value' => 'foo'],
                ['value' => 'bar'],
                ['value' => 'baz'],
            ],
            'objectsWithAlias' => [
                ['value' => 'foo'],
                ['value' => 'bar'],
                ['value' => 'baz'],
            ],
            'nonEmptyListOfStrings' => ['foo', 'bar', 'baz'],
            'listOfStringsWithKeys' => [
                'foo' => 'foo',
                'bar' => 'bar',
                'baz' => 'baz',
            ],
            'nonEmptyListOfStringsWithKeys' => [
                'foo' => 'foo',
                'bar' => 'bar',
                'baz' => 'baz',
            ],
        ];

        foreach ([ListValues::class, ListValuesWithConstructor::class] as $class) {
            try {
                $result = $this->mapperBuilder->mapper()->map($class, $source);
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
            self::assertSame($source['nonEmptyListOfStrings'], $result->nonEmptyListOfStrings);
            self::assertSame(array_values($source['listOfStringsWithKeys']), $result->listOfStringsWithKeys);
            self::assertSame(array_values($source['nonEmptyListOfStringsWithKeys']), $result->nonEmptyListOfStringsWithKeys);
        }
    }

    public function test_empty_list_in_non_empty_list_throws_exception(): void
    {
        try {
            $this->mapperBuilder->mapper()->map(ListValues::class, [
                'nonEmptyListOfStrings' => [],
            ]);
        } catch (MappingError $exception) {
            $error = $exception->describe()['nonEmptyListOfStrings'][0];

            self::assertInstanceOf(InvalidNodeValue::class, $error);
            self::assertSame(1630678334, $error->getCode());
            self::assertSame('Empty array is not accepted by `non-empty-list<string>`.', $error->getMessage());
        }
    }

    public function test_value_that_cannot_be_casted_throws_exception(): void
    {
        try {
            $this->mapperBuilder->mapper()->map(ListValues::class, [
                'integers' => ['foo'],
            ]);
        } catch (MappingError $exception) {
            $error = $exception->describe()['integers.0'][0];

            self::assertInstanceOf(CannotCastToScalarValue::class, $error);
            self::assertSame(1618736242, $error->getCode());
            self::assertSame('Cannot cast value of type `string` to `int`.', $error->getMessage());
        }
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

    /** @var non-empty-list<string> */
    public array $nonEmptyListOfStrings = ['foo'];

    /** @var list<string> */
    public array $listOfStringsWithKeys;

    /** @var non-empty-list<string> */
    public array $nonEmptyListOfStringsWithKeys;
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
     * @param non-empty-list<string> $nonEmptyListOfStrings
     * @param list<string> $listOfStringsWithKeys
     * @param non-empty-list<string> $nonEmptyListOfStringsWithKeys
     */
    public function __construct(
        array $booleans,
        array $floats,
        array $integers,
        array $strings,
        array $objects,
        array $objectsWithAlias,
        array $nonEmptyListOfStrings,
        array $listOfStringsWithKeys,
        array $nonEmptyListOfStringsWithKeys
    ) {
        $this->booleans = $booleans;
        $this->floats = $floats;
        $this->integers = $integers;
        $this->strings = $strings;
        $this->objects = $objects;
        $this->objectsWithAlias = $objectsWithAlias;
        $this->nonEmptyListOfStrings = $nonEmptyListOfStrings;
        $this->listOfStringsWithKeys = $listOfStringsWithKeys;
        $this->nonEmptyListOfStringsWithKeys = $nonEmptyListOfStringsWithKeys;
    }
}
