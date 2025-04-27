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

    public function test_clear_entries_clears_everything(): void
    {
        $delegate = new FakeCache();
        $cache = new RuntimeCache($delegate);

        $cache->set('foo', new CacheEntry('fn () => "foo"'));
        $cache->set('bar', new CacheEntry('fn () => "bar"'));

        self::assertSame(2, $delegate->countEntries());

        $cache->clear();

        self::assertNull($cache->get('foo'));
        self::assertNull($cache->get('bar'));
        self::assertSame(0, $delegate->countEntries());
    }
}
