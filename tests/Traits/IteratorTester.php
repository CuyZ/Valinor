<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Traits;

use PHPUnit\Framework\TestCase;

use function array_keys;
use function count;

/**
 * @mixin TestCase
 */
trait IteratorTester
{
    /**
     * @param iterable<mixed> $traversable
     * @param mixed[] $items
     */
    private function checkIterable(iterable $traversable, array $items): void
    {
        $index = 0;

        $keys = array_keys($items);

        foreach ($traversable as $key => $item) {
            $currentKey = $keys[$index++];

            self::assertSame($key, $currentKey);
            self::assertSame($item, $items[$currentKey]);
        }

        self::assertSame(count($items), $index);

        $index = 0;

        foreach ($traversable as $key => $item) {
            $currentKey = $keys[$index++];

            self::assertSame($key, $currentKey);
            self::assertSame($item, $items[$currentKey]);
        }

        self::assertSame(count($items), $index);
    }
}
