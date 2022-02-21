<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Source;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Source\Modifier\PathMapping;
use CuyZ\Valinor\Tests\Integration\IntegrationTest;

final class PathMappingTest extends IntegrationTest
{
    public function test_path_with_sub_paths_are_mapped(): void
    {
        $map = [
            'A1' => 'newA1',
            'A1.B1' => 'value',
            'A2' => 'newA2',
            'A2.*.B1' => 'value',
            'A3' => 'newA3',
            'A3.B1' => 'newB1',
            'A3.B2' => 'newB2',
            'A3.*.C' => 'value',
            'A4' => 'newA4',
            'A4.B' => 'newB',
            'A4.B.*.B1' => 'value',
        ];

        $keys = array_keys($map);
        shuffle($keys);
        $randomMap = [];

        foreach ($keys as $key) {
            $randomMap[$key] = $map[$key];
        }

        try {
            $object = $this->mapperBuilder->mapper()->map(
                SomeRootClass::class,
                new PathMapping(
                    [
                        'A1' => [
                            'B1' => 'foo',
                        ],
                        'A2' => [
                            ['B1' => 'bar'],
                            ['B1' => 'buz'],
                        ],
                        'A3' => [
                            'B1' => ['C' => 'biz'],
                            'B2' => ['C' => 'boz'],
                        ],
                        'A4' => [
                            'B' => [
                                ['B1' => 'faz'],
                                ['B1' => 'fyz'],
                            ],
                        ],
                    ],
                    $randomMap
                )
            );
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $object->newA1->value);

        self::assertCount(2, $object->newA2);
        self::assertSame('bar', $object->newA2[0]->value);
        self::assertSame('buz', $object->newA2[1]->value);

        self::assertSame('biz', $object->newA3->newB1->value);
        self::assertSame('boz', $object->newA3->newB2->value);

        self::assertCount(2, $object->newA4->newB);
        self::assertSame('faz', $object->newA4->newB[0]->value);
        self::assertSame('fyz', $object->newA4->newB[1]->value);
    }
}

class SomeRootClass
{
    public SomeClassWithOneProperty $newA1;

    /** @var array<SomeClassWithOneProperty> */
    public array $newA2;

    public SomeClassWithTwoProperties $newA3;

    public SomeClassWithArrayProperty $newA4;
}

class SomeClassWithOneProperty
{
    public string $value;
}

class SomeClassWithTwoProperties
{
    public SomeClassWithOneProperty $newB1;
    public SomeClassWithOneProperty $newB2;
}

class SomeClassWithArrayProperty
{
    /** @var array<SomeClassWithOneProperty> */
    public array $newB;
}
