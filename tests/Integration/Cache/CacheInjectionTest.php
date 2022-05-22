<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Cache;

use CuyZ\Valinor\Cache\FileSystemCache;
use CuyZ\Valinor\Cache\FileWatchingCache;
use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Tests\Integration\IntegrationTest;
use CuyZ\Valinor\Tests\Integration\Mapping\Fixture\SimpleObject;
use org\bovigo\vfs\vfsStream;

use function strtoupper;

final class CacheInjectionTest extends IntegrationTest
{
    public function test_cache_entries_are_written_during_mapping(): void
    {
        $files = vfsStream::setup('cache-dir');

        $cache = new FileSystemCache($files->url());
        $cache = new FileWatchingCache($cache);

        self::assertFalse($files->hasChildren());

        $object = (new MapperBuilder())
            ->withCache($cache)
            // The cache should be able to cache function definitions…
            ->alter(fn (string $value): string => strtoupper($value))
            ->mapper()
            // …as well as class definitions.
            ->map(SimpleObject::class, 'foo');

        self::assertSame('FOO', $object->value);

        self::assertTrue($files->hasChildren());
    }
}
