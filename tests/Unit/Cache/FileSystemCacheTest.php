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

use function count;
use function file_put_contents;
use function mkdir;
use function umask;

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
        $this->expectExceptionMessageMatches('/Compiled php cache file `[^`]+` has corrupted value./');

        $this->cache->get('foo');
    }

    public function test_cache_directory_not_writable_throws_exception(): void
    {
        $this->expectException(CacheDirectoryNotWritable::class);
        $this->expectExceptionMessage("Provided directory `{$this->files->url()}` is not writable.");

        $this->files->chmod(0444);

        $this->cache->set('foo', new CacheEntry('fn () => "foo"'));
    }

    public function test_temporary_dir_has_correct_permissions(): void
    {
        self::assertFalse($this->files->hasChild('.valinor.tmp'));

        $this->cache->set('foo', new CacheEntry('fn () => "foo"'));

        self::assertTrue($this->files->hasChild('.valinor.tmp'));
        self::assertSame(0777, $this->files->getChild('.valinor.tmp')->getPermissions());
    }

    public function test_temporary_cache_file_not_writable_throws_exception(): void
    {
        $this->expectException(CompiledPhpCacheFileNotWritten::class);
        $this->expectExceptionMessageMatches('/^File `[^`]+.valinor.tmp[^`]+` could not be written\.$/');

        (vfsStream::newDirectory('.valinor.tmp'))
            ->chmod(0444)
            ->at($this->files);

        $this->cache->set('foo', new CacheEntry('fn () => "foo"'));
    }

    public function test_cache_file_not_writable_throws_exception(): void
    {
        $this->expectException(CompiledPhpCacheFileNotWritten::class);
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

    public function test_cache_file_has_correct_permissions(): void
    {
        $this->cache->set('foo', new CacheEntry('fn () => "foo"'));

        $file = $this->files->getChildren()[1];

        self::assertSame(0666 & ~umask(), $file->getPermissions());
    }

    public function test_clear_entries_clears_everything(): void
    {
        $this->cache->set('foo', new CacheEntry('fn () => "foo"'));
        $this->cache->set('bar', new CacheEntry('fn () => "bar"'));

        self::assertSame(3, count($this->files->getChildren()));

        $this->cache->clear();

        self::assertDirectoryDoesNotExist($this->files->url());
        self::assertSame(0, count($this->files->getChildren()));
    }

    public function test_clear_entries_when_external_file_was_added_does_not_remove_it(): void
    {
        $this->cache->set('foo', new CacheEntry('fn () => "foo"'));
        $this->cache->set('bar', new CacheEntry('fn () => "bar"'));

        $externalFile = $this->files->url() . '/external-file.php';
        file_put_contents($externalFile, 'some-external-content');

        self::assertSame(4, count($this->files->getChildren()));

        $this->cache->clear();

        self::assertDirectoryExists($this->files->url());
        self::assertFileExists($externalFile);
        self::assertSame(1, count($this->files->getChildren()));
    }

    public function test_clear_when_cache_directory_does_not_exist_returns_early(): void
    {
        $nonExistentDir = vfsStream::url('non-existent-dir');
        $cache = new FileSystemCache($nonExistentDir);

        $cache->clear();

        self::assertDirectoryDoesNotExist($nonExistentDir);
    }

    public function test_clear_entries_when_subdirectory_exists_does_not_remove_root_directory(): void
    {
        $subdirectory = $this->files->url() . '/custom-subdirectory';
        mkdir($subdirectory);

        $this->cache->clear();

        self::assertDirectoryExists($this->files->url());
        self::assertDirectoryExists($subdirectory);
    }
}
