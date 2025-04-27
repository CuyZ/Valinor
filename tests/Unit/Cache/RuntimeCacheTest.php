<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Cache;

use CuyZ\Valinor\Cache\CacheEntry;
use CuyZ\Valinor\Cache\RuntimeCache;
use CuyZ\Valinor\Tests\Fake\Cache\FakeCache;
use PHPUnit\Framework\TestCase;

final class RuntimeCacheTest extends TestCase
{
    public function test_value_is_fetched_only_once_from_delegate(): void
    {
        $delegate = new FakeCache();
        $cache = new RuntimeCache($delegate);

        $cache->set('foo', new CacheEntry('fn () => "foo"'));

        $valueA = $cache->get('foo');
        $valueB = $cache->get('foo');

        self::assertSame('foo', $valueA);
        self::assertSame('foo', $valueB);

        self::assertSame(1, $delegate->timesEntryWasFetched('foo'));
    }
}
