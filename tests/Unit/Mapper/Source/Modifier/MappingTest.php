<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Mapper\Source\Modifier;

use CuyZ\Valinor\Mapper\Source\Modifier\Mapping;
use CuyZ\Valinor\Tests\Unit\UnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class MappingTest extends UnitTestCase
{
    /**
     * @param array<string> $keys
     */
    #[DataProvider('mappings_data_provider')]
    public function test_matches_string_key_at_sub_level(
        array $keys,
        string $to,
        string|int $targetKey,
        int $targetDepth,
        bool $expectedMatch,
        ?string $expectedTo
    ): void {
        $mapping = new Mapping($keys, $to);

        self::assertSame($expectedMatch, $mapping->matches($targetKey, $targetDepth));
        self::assertSame($expectedTo, $mapping->findMappedKey($targetKey, $targetDepth));
    }

    /**
     * @return array<mixed>
     */
    public static function mappings_data_provider(): array
    {
        return [
            [
                'keys' => ['A'],
                'to' => 'newA',
                'targetKey' => 'A',
                'targetDepth' => 0,
                'expectedMatch' => true,
                'expectedTo' => 'newA',
            ],
            [
                'keys' => ['A', 'B'],
                'to' => 'newB',
                'targetKey' => 'B',
                'targetDepth' => 1,
                'expectedMatch' => true,
                'expectedTo' => 'newB',
            ],
            [
                'keys' => ['A', 'B', 'C'],
                'to' => 'newB',
                'targetKey' => 'B',
                'targetDepth' => 1,
                'expectedMatch' => true,
                'expectedTo' => null,
            ],
            [
                'keys' => ['A', '*', 'B'],
                'to' => 'newB',
                'targetKey' => 'B',
                'targetDepth' => 1,
                'expectedMatch' => true,
                'expectedTo' => null,
            ],
            [
                'keys' => ['A', '*', 'B'],
                'to' => 'newB',
                'targetKey' => 'B',
                'targetDepth' => 2,
                'expectedMatch' => true,
                'expectedTo' => 'newB',
            ],
        ];
    }
}
