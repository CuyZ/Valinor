<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Utility\Path;

use CuyZ\Valinor\Utility\Path\PathMapper;
use PHPUnit\Framework\TestCase;

final class PathMapperTest extends TestCase
{
    public function test_root_path_is_mapped(): void
    {
        $result = (new PathMapper())->map(
            ['A' => 'bar'],
            ['A' => 'new_A']
        );

        self::assertSame(['new_A' => 'bar'], $result);
    }

    public function test_sub_path_is_mapped(): void
    {
        $result = (new PathMapper())->map(
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
        ], $result);
    }

    public function test_root_iterable_path_is_mapped(): void
    {
        $result = (new PathMapper())->map(
            [
                ['A' => 'bar'],
                ['A' => 'buz'],
            ],
            ['*.A' => 'new_A']
        );

        self::assertSame([
            ['new_A' => 'bar'],
            ['new_A' => 'buz'],
        ], $result);
    }

    public function test_sub_iterable_numeric_path_is_mapped(): void
    {
        $result = (new PathMapper())->map(
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
        ], $result);
    }

    public function test_sub_iterable_string_path_is_mapped(): void
    {
        $result = (new PathMapper())->map(
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
        ], $result);
    }

    public function test_path_with_sub_paths_are_mapped(): void
    {
        $result = (new PathMapper())->map(
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
        ], $result);
    }

    public function test_conflicting_paths_are_mapped(): void
    {
        $result = (new PathMapper())->map(
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
        ], $result);
    }

    public function test_sub_iterable_numeric_path_with_sub_key_is_mapped(): void
    {
        $result = (new PathMapper())->map(
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
        ], $result);
    }

    public function test_sub_iterable_string_path_with_sub_key_is_mapped(): void
    {
        $result = (new PathMapper())->map(
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
        ], $result);
    }
}
