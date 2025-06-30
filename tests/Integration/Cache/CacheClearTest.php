<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Cache;

use CuyZ\Valinor\Normalizer\Format;
use CuyZ\Valinor\Tests\Fake\Cache\FakeCache;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;

final class CacheClearTest extends IntegrationTestCase
{
    public function test_call_mapper_builder_clear_cache_clears_cache(): void
    {
        $cache = new FakeCache();

        $mapperBuilder = $this->mapperBuilder()->withCache($cache);

        $mapperBuilder->warmupCacheFor('array<stdClass>');

        self::assertGreaterThan(0, $cache->countEntries());

        $mapperBuilder->clearCache();

        self::assertSame(0, $cache->countEntries());
    }

    public function test_call_normalizer_builder_clear_cache_clears_cache(): void
    {
        $cache = new FakeCache();

        $mapperBuilder = $this->normalizerBuilder()->withCache($cache);

        // @phpstan-ignore method.resultUnused
        $mapperBuilder->normalizer(Format::array())->normalize('foo');

        self::assertGreaterThan(0, $cache->countEntries());

        $mapperBuilder->clearCache();

        self::assertSame(0, $cache->countEntries());
    }
}
