<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Cache;

use CuyZ\Valinor\Cache\CacheEntry;
use CuyZ\Valinor\Cache\KeySanitizerCache;
use CuyZ\Valinor\Library\Settings;
use CuyZ\Valinor\Tests\Fake\Cache\FakeCache;
use PHPUnit\Framework\TestCase;

use function array_key_first;

final class KeySanitizerCacheTest extends TestCase
{
    public function test_set_value_sets_value_in_delegate_with_changed_key(): void
    {
        $delegate = new FakeCache();
        $cache = new KeySanitizerCache($delegate, new Settings());

        $cache->set("some-key-\0some-suffix", new CacheEntry('fn () => "foo"'));

        $cacheEntries = $delegate->entriesThatWereSet();

        self::assertCount(1, $cacheEntries);
        self::assertMatchesRegularExpression('/some-key-[a-z0-9]{32}/', array_key_first($cacheEntries));
    }

    public function test_get_value_returns_value_from_delegate(): void
    {
        $cache = new KeySanitizerCache(new FakeCache(), new Settings());

        $cache->set("some-key-\0some-suffix", new CacheEntry('fn () => "foo"'));

        self::assertSame('foo', $cache->get("some-key-\0some-suffix"));
    }

    public function test_clear_entries_clears_everything(): void
    {
        $delegate = new FakeCache();
        $cache = new KeySanitizerCache($delegate, new Settings());

        $cache->set('foo', new CacheEntry('fn () => "foo"'));
        $cache->set('bar', new CacheEntry('fn () => "bar"'));

        self::assertSame(2, $delegate->countEntries());

        $cache->clear();

        self::assertNull($cache->get('foo'));
        self::assertNull($cache->get('bar'));
        self::assertSame(0, $delegate->countEntries());
    }
}
