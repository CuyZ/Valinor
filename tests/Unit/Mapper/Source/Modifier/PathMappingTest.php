<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Mapper\Source\Modifier;

use CuyZ\Valinor\Mapper\Source\Modifier\PathMapping;
use PHPUnit\Framework\TestCase;

final class PathMappingTest extends TestCase
{
    public function test_root_path_is_mapped(): void
    {
        $source = new PathMapping(
            ['A' => 'bar'],
            ['A' => 'new_A']
        );

        self::assertSame(['new_A' => 'bar'], iterator_to_array($source));
    }

    public function test_root_integer_path_is_mapped(): void
    {
        $source = new PathMapping(
            [0 => 'bar'],
            [0 => 'new_A']
        );

        self::assertSame(['new_A' => 'bar'], iterator_to_array($source));
    }

    public function test_sub_path_is_mapped(): void
    {
        $source = new PathMapping(
            [
                'A' => [
                    'B' => 'foo',
                ],
            ],
            ['A.B' => 'new_B']
        );

        self::assertSame([
            'A' => [
                'new_B' => 'foo',
            ],
        ], iterator_to_array($source));
    }

    public function test_sub_path_with_integer_is_mapped(): void
    {
        $source = new PathMapping(
            [
                'A' => [
                    1 => 'foo',
                ],
            ],
            ['A.1' => 'new_B']
        );

        self::assertSame([
            'A' => [
                'new_B' => 'foo',
            ],
        ], iterator_to_array($source));
    }

    public function test_root_iterable_path_is_mapped(): void
    {
        $source = new PathMapping(
            [
                ['A' => 'bar'],
                ['A' => 'buz'],
            ],
            ['*.A' => 'new_A']
        );

        self::assertSame([
            ['new_A' => 'bar'],
            ['new_A' => 'buz'],
        ], iterator_to_array($source));
    }

    public function test_root_iterable_path_with_integer_is_mapped(): void
    {
        $source = new PathMapping(
            [
                [2 => 'bar'],
                [2 => 'buz'],
            ],
            ['*.2' => 'new_A']
        );

        self::assertSame([
            ['new_A' => 'bar'],
            ['new_A' => 'buz'],
        ], iterator_to_array($source));
    }

    public function test_sub_iterable_numeric_path_is_mapped(): void
    {
        $source = new PathMapping(
            [
                'A' => [
                    ['C' => 'bar'],
                    ['C' => 'buz'],
                ],
            ],
            ['A.*.C' => 'new_C']
        );

        self::assertSame([
            'A' => [
                ['new_C' => 'bar'],
                ['new_C' => 'buz'],
            ],
        ], iterator_to_array($source));
    }

    public function test_sub_iterable_string_path_is_mapped(): void
    {
        $source = new PathMapping(
            [
                'A' => [
                    'B1' => ['C' => 'bar'],
                    'B2' => ['C' => 'buz'],
                ],
            ],
            ['A.*.C' => 'new_C'],
        );

        self::assertSame([
            'A' => [
                'B1' => ['new_C' => 'bar'],
                'B2' => ['new_C' => 'buz'],
            ],
        ], iterator_to_array($source));
    }

    public function test_path_with_sub_paths_are_mapped(): void
    {
        $source = new PathMapping(
            [
                'A' => [
                    ['B' => 'bar'],
                    ['B' => 'buz'],
                ],
            ],
            [
                'A' => 'new_A',
                'A.*.B' => 'new_B',
            ]
        );

        self::assertSame([
            'new_A' => [
                ['new_B' => 'bar'],
                ['new_B' => 'buz'],
            ],
        ], iterator_to_array($source));
    }

    public function test_conflicting_paths_are_mapped(): void
    {
        $source = new PathMapping(
            [
                'A1' => [
                    'B' => 'foo',
                ],
                'A2' => [
                    'B' => 'bar',
                ],
            ],
            [
                'A1.B' => 'new_B1',
            ]
        );

        self::assertSame([
            'A1' => [
                'new_B1' => 'foo',
            ],
            'A2' => [
                'B' => 'bar',
            ],
        ], iterator_to_array($source));
    }

    public function test_sub_iterable_numeric_path_with_sub_key_is_mapped(): void
    {
        $source = new PathMapping(
            [
                'A' => [
                    [
                        'B' => ['C' => 'bar'],
                    ],
                    [
                        'B' => ['C' => 'buz'],
                    ],
                ],
            ],
            [
                'A.*.B.C' => 'new_C',
            ]
        );

        self::assertSame([
            'A' => [
                [
                    'B' => ['new_C' => 'bar'],
                ],
                [
                    'B' => ['new_C' => 'buz'],
                ],
            ],
        ], iterator_to_array($source));
    }

    public function test_sub_iterable_string_path_with_sub_key_is_mapped(): void
    {
        $source = new PathMapping(
            [
                'A' => [
                    'B1' => [
                        'C' => ['D' => 'bar'],
                    ],
                    'B2' => [
                        'C' => ['D' => 'buz'],
                    ],
                ],
            ],
            [
                'A.*.C.D' => 'new_D',
            ]
        );

        self::assertSame([
            'A' => [
                'B1' => [
                    'C' => ['new_D' => 'bar'],
                ],
                'B2' => [
                    'C' => ['new_D' => 'buz'],
                ],
            ],
        ], iterator_to_array($source));
    }
}
