<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Cache;

use CuyZ\Valinor\Cache\CacheEntry;
use CuyZ\Valinor\Cache\FileWatchingCache;
use CuyZ\Valinor\Tests\Fake\Cache\FakeCache;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;

use function time;
use function unlink;

final class FileWatchingCacheTest extends TestCase
{
    private vfsStreamDirectory $files;

    private FakeCache $delegateCache;

    /** @var FileWatchingCache<mixed> */
    private FileWatchingCache $cache;

    protected function setUp(): void
    {
        parent::setUp();

        $this->files = vfsStream::setup();

        $this->delegateCache = new FakeCache();
        $this->cache = new FileWatchingCache($this->delegateCache);
    }

    public function test_deleting_watched_file_invalidates_cache(): void
    {
        $fileA = (vfsStream::newFile('ObjectA.php'))->at($this->files);
        $fileB = (vfsStream::newFile('ObjectB.php'))->at($this->files);
        $fileC = (vfsStream::newFile('ObjectC.php'))->at($this->files);

        $cacheEntry = new CacheEntry(
            code: 'fn () => "foo"',
            filesToWatch: [
                $fileA->url(),
                $fileB->url(),
                $fileC->url(),
            ],
        );

        $this->cache->set('foo', $cacheEntry);

        // First cache fetch: the watched file exists and is not modified
        self::assertSame('foo', $this->cache->get('foo'));

        // The file is deleted, so the cache should be invalidated
        unlink($fileB->url());

        self::assertSame(null, $this->cache->get('foo'));

        // We set a new cache entry
        $this->cache->set('foo', $cacheEntry);

        self::assertSame('foo', $this->cache->get('foo'));
    }

    public function test_modifying_watched_file_invalidates_cache(): void
    {
        $fileA = (vfsStream::newFile('ObjectA.php'))->at($this->files);
        $fileB = (vfsStream::newFile('ObjectB.php'))->at($this->files);
        $fileC = (vfsStream::newFile('ObjectC.php'))->at($this->files);

        $cacheEntry = new CacheEntry(
            code: 'fn () => "foo"',
            filesToWatch: [
                $fileA->url(),
                $fileB->url(),
                $fileC->url(),
            ],
        );

        $this->cache->set('foo', $cacheEntry);

        // First cache fetch: the watched file exists and is not modified
        self::assertSame('foo', $this->cache->get('foo'));

        // The file is modified, so the cache should be invalidated
        $fileB->lastModified(time() + 10)->at($this->files);

        self::assertSame(null, $this->cache->get('foo'));

        // We set a new cache entry
        $this->cache->set('foo', $cacheEntry);

        self::assertSame('foo', $this->cache->get('foo'));
    }

    public function test_invalid_file_path_is_ignored_by_watcher(): void
    {
        $file = (vfsStream::newFile('ObjectA.php'))->at($this->files);

        $cacheEntry = new CacheEntry(
            code: 'fn () => "foo"',
            filesToWatch: [
                'not/existing/file',
                $file->url(),
            ],
        );

        $this->cache->set('foo', $cacheEntry);

        self::assertSame('foo', $this->cache->get('foo'));
    }

    public function test_get_entry_gets_timestamps_on_delegate_only_once(): void
    {
        $cacheEntry = new CacheEntry(
            code: 'fn () => "foo"',
            filesToWatch: [],
        );

        $this->cache->set('foo', $cacheEntry);

        // A little hack to reset the memoized timestamps values in the cache,
        //  to simulate that the cache was not yet set in the current request.
        (fn () => $this->timestamps = [])->call($this->cache);

        $this->cache->get('foo');
        $this->cache->get('foo');

        self::assertSame(1, $this->delegateCache->timesEntryWasFetched('foo.timestamps'));
    }

    public function test_get_entry_that_was_not_set_returns_null(): void
    {
        self::assertSame(null, $this->cache->get('foo'));
    }

    public function test_clear_entries_clears_everything(): void
    {
        $this->cache->set('foo', new CacheEntry('fn () => "foo"'));
        $this->cache->set('bar', new CacheEntry('fn () => "bar"'));

        self::assertSame(4, $this->delegateCache->countEntries());

        $this->cache->clear();

        self::assertNull($this->cache->get('foo'));
        self::assertNull($this->cache->get('bar'));
        self::assertSame(0, $this->delegateCache->countEntries());
    }
}
