<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Cache;

use CuyZ\Valinor\Cache\CacheEntry;
use CuyZ\Valinor\Cache\Exception\CacheDirectoryNotWritable;
use CuyZ\Valinor\Cache\Exception\CompiledPhpCacheFileNotWritten;
use CuyZ\Valinor\Cache\Exception\CorruptedCompiledPhpCacheFile;
use CuyZ\Valinor\Cache\FileSystemCache;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;

final class FileSystemCacheTest extends TestCase
{
    private vfsStreamDirectory $files;

    /** @var FileSystemCache<mixed> */
    private FileSystemCache $cache;

    protected function setUp(): void
    {
        parent::setUp();

        $this->files = vfsStream::setup('cache-dir');

        $this->cache = new FileSystemCache($this->files->url());
    }

    public function test_set_cache_sets_cache(): void
    {
        $code = 'fn () => "foo"';

        $this->cache->set('foo', new CacheEntry($code));

        self::assertSame('foo', $this->cache->get('foo'));
    }

    public function test_get_missing_cache_entry_returns_null(): void
    {
        $value = $this->cache->get('foo');

        self::assertSame(null, $value);
    }

    public function test_corrupted_file_throws_exception(): void
    {
        $code = 'fn () => invalid php code';

        $this->cache->set('foo', new CacheEntry($code));

        $this->expectException(CorruptedCompiledPhpCacheFile::class);
        $this->expectExceptionCode(1628949607);
        $this->expectExceptionMessageMatches('/Compiled php cache file `[^`]+` has corrupted value./');

        $this->cache->get('foo');
    }

    public function test_cache_directory_not_writable_throws_exception(): void
    {
        $this->expectException(CacheDirectoryNotWritable::class);
        $this->expectExceptionCode(1616445016);
        $this->expectExceptionMessage("Provided directory `{$this->files->url()}` is not writable.");

        $this->files->chmod(0444);

        $this->cache->set('foo', new CacheEntry('fn () => "foo"'));
    }

    public function test_temporary_cache_file_not_writable_throws_exception(): void
    {
        $this->expectException(CompiledPhpCacheFileNotWritten::class);
        $this->expectExceptionCode(1616445695);
        $this->expectExceptionMessageMatches('/^File `[^`]+.valinor.tmp[^`]+` could not be written\.$/');

        (vfsStream::newDirectory('.valinor.tmp'))
            ->chmod(0444)
            ->at($this->files);

        $this->cache->set('foo', new CacheEntry('fn () => "foo"'));
    }

    public function test_cache_file_not_writable_throws_exception(): void
    {
        $this->expectException(CompiledPhpCacheFileNotWritten::class);
        $this->expectExceptionCode(1616445695);
        $this->expectExceptionMessageMatches('/^File `[^`]+` could not be written\.$/');

        (vfsStream::newDirectory('.valinor.tmp'))->at($this->files);

        $this->files->chmod(0444);

        $this->cache->set('foo', new CacheEntry('fn () => "foo"'));
    }

    public function test_temporary_cache_file_is_always_deleted(): void
    {
        $tmpDirectory = vfsStream::newDirectory('.valinor.tmp');
        $tmpDirectory->at($this->files);

        $this->files->chmod(0444);

        try {
            $this->cache->set('foo', new CacheEntry('fn () => "foo"'));
        } catch (CompiledPhpCacheFileNotWritten) {
        }

        self::assertEmpty($tmpDirectory->getChildren());
    }
}
