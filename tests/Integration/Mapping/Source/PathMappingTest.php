<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Source;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Source\Modifier\PathMapping;
use CuyZ\Valinor\Tests\Integration\IntegrationTest;

final class PathMappingTest extends IntegrationTest
{
    public function test_root_path_is_mapped(): void
    {
        try {
            $object = $this->mapperBuilder->mapper()->map(
                SomeClassWithOneProperty::class,
                new PathMapping(
                    ['foo' => 'bar'],
                    ['foo' => 'value']
                )
            );
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('bar', $object->value);
    }

    public function test_sub_path_is_mapped(): void
    {
        try {
            $object = $this->mapperBuilder->mapper()->map(
                SomeClassWithOneSubLevel::class,
                new PathMapping(
                    ['subValue' => ['foo' => 'bar']],
                    ['subValue.foo' => 'value']
                )
            );
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('bar', $object->subValue->value);
    }

    public function test_root_iterable_path_is_mapped(): void
    {
        try {
            $objects = $this->mapperBuilder->mapper()->map(
                SomeClassWithOneProperty::class . '[]',
                new PathMapping(
                    [
                        ['a1' => 'bar'],
                        ['a1' => 'buz'],
                    ],
                    ['*.a1' => 'value']
                )
            );
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertCount(2, $objects);
        self::assertSame('bar', $objects[0]->value);
        self::assertSame('buz', $objects[1]->value);
    }

    public function test_sub_iterable_numeric_path_is_mapped(): void
    {
        try {
            $object = $this->mapperBuilder->mapper()->map(
                SomeClassWithSubLevelArray::class,
                new PathMapping(
                    [
                        'subArrayValue' => [
                            ['foo' => 'bar'],
                            ['foo' => 'buz'],
                        ],
                    ],
                    ['subArrayValue.*.foo' => 'value']
                )
            );
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertCount(2, $object->subArrayValue->values);
        self::assertSame('bar', $object->subArrayValue->values[0]->value);
        self::assertSame('buz', $object->subArrayValue->values[1]->value);
    }

    public function test_sub_iterable_string_path_is_mapped(): void
    {
        try {
            $object = $this->mapperBuilder->mapper()->map(
                SomeClassWithSubLevelArray::class,
                new PathMapping(
                    [
                        'subArrayValue' => [
                            'bar' => ['foo' => 'bar'],
                            'buz' => ['foo' => 'buz'],
                        ],
                    ],
                    ['subArrayValue.*.foo' => 'value']
                )
            );
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertCount(2, $object->subArrayValue->values);
        self::assertSame('bar', $object->subArrayValue->values['bar']->value);
        self::assertSame('buz', $object->subArrayValue->values['buz']->value);
    }

    public function test_path_with_sub_paths_are_mapped(): void
    {
        try {
            $object = $this->mapperBuilder->mapper()->map(
                SomeClassWithSubLevelArray::class,
                new PathMapping(
                    [
                        'A' => [
                            ['B' => 'bar'],
                            ['B' => 'buz'],
                        ],
                    ],
                    [
                        'A' => 'subArrayValue',
                        'A.*.B' => 'value',
                    ]
                )
            );
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertCount(2, $object->subArrayValue->values);
        self::assertSame('bar', $object->subArrayValue->values[0]->value);
        self::assertSame('buz', $object->subArrayValue->values[1]->value);
    }
}

class SomeClassWithOneProperty
{
    public string $value;
}

class SomeClassWithOneSubLevel
{
    public SomeClassWithOneProperty $subValue;
}

class SomeClassWithSubLevelArray
{
    public SomeClassWithArray $subArrayValue;
}

class SomeClassWithArray
{
    /** @var SomeClassWithOneProperty[] */
    public array $values;
}
