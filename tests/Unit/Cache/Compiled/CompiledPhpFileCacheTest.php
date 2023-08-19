<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Cache\Compiled;

use CuyZ\Valinor\Cache\Compiled\CompiledPhpFileCache;
use CuyZ\Valinor\Cache\Exception\CacheDirectoryNotWritable;
use CuyZ\Valinor\Cache\Exception\CompiledPhpCacheFileNotWritten;
use CuyZ\Valinor\Cache\Exception\CorruptedCompiledPhpCacheFile;
use CuyZ\Valinor\Tests\Fake\Cache\Compiled\FakeCacheCompiler;
use DateTime;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamFile;
use PHPUnit\Framework\TestCase;
use RuntimeException;

use function iterator_to_array;
use function rmdir;

final class CompiledPhpFileCacheTest extends TestCase
{
    private vfsStreamDirectory $files;

    /** @var CompiledPhpFileCache<mixed> */
    private CompiledPhpFileCache $cache;

    protected function setUp(): void
    {
        parent::setUp();

        $this->files = vfsStream::setup('cache-dir');

        $this->cache = new CompiledPhpFileCache(vfsStream::url('cache-dir'), new FakeCacheCompiler());
    }

    public function test_warmup_creates_temporary_dir(): void
    {
        self::assertFalse($this->files->hasChild('.valinor.tmp'));

        $this->cache->warmup();

        self::assertTrue($this->files->hasChild('.valinor.tmp'));
    }

    public function test_set_cache_sets_cache(): void
    {
        self::assertFalse($this->cache->has('foo'));
        self::assertTrue($this->cache->set('foo', 'foo'));
        self::assertTrue($this->cache->has('foo'));
        self::assertSame('foo', $this->cache->get('foo'));
    }

    public function test_set_cache_with_integer_ttl_saves_entry(): void
    {
        $this->cache->set('foo', 'foo', 10);

        self::assertTrue($this->cache->has('foo'));
    }

    public function test_set_cache_with_date_interval_ttl_saves_entry(): void
    {
        $this->cache->set('foo', 'foo', (new DateTime())->diff(new DateTime('+10 seconds')));

        self::assertTrue($this->cache->has('foo'));
    }

    public function test_set_cache_with_negative_ttl_saves_invalid_entry(): void
    {
        $this->cache->set('foo', 'foo', -10);

        self::assertFalse($this->cache->has('foo'));
    }

    public function test_get_missing_cache_entry_returns_default(): void
    {
        $value = $this->cache->get('foo', 'bar');

        self::assertSame('bar', $value);
    }

    public function test_delete_entry_deletes_entry(): void
    {
        $this->cache->set('foo', 'foo');
        $this->cache->delete('foo');

        self::assertFalse($this->cache->has('foo'));
    }

    public function test_delete_non_existent_entry_returns_true(): void
    {
        self::assertTrue($this->cache->delete('non-existing-entry'));
    }

    public function test_cannot_delete_cache_file_returns_false(): void
    {
        $this->cache->set('foo', 'foo');

        $this->files->chmod(0444);

        self::assertFalse($this->cache->delete('foo'));
    }

    public function test_clear_caches_clears_all_entries(): void
    {
        $this->cache->set('foo', 'foo');
        $this->cache->set('bar', 'bar');

        $cleared = $this->cache->clear();

        self::assertTrue($cleared);
        self::assertFalse($this->cache->has('foo'));
        self::assertFalse($this->cache->has('bar'));
    }

    public function test_clear_cannot_delete_cache_file_returns_false(): void
    {
        $this->cache->set('foo', 'foo');

        $this->files->chmod(0444);

        self::assertFalse($this->cache->clear());
    }

    public function test_clear_caches_when_cache_directory_does_not_exists_returns_true(): void
    {
        rmdir($this->files->url());

        self::assertTrue($this->cache->clear());
    }

    public function test_set_multiple_values_sets_values(): void
    {
        self::assertTrue($this->cache->setMultiple([
            'foo' => 'foo',
            'bar' => 'bar',
        ]));

        $result = iterator_to_array($this->cache->getMultiple(['foo', 'bar']));

        self::assertTrue($this->cache->has('foo'));
        self::assertTrue($this->cache->has('bar'));

        self::assertSame('foo', $result['foo']);
        self::assertSame('bar', $result['bar']);
    }

    public function test_delete_entries_deletes_correct_entries(): void
    {
        $this->cache->setMultiple([
            'foo' => 'foo',
            'bar' => 'bar',
            'baz' => 'baz',
        ]);

        self::assertTrue($this->cache->deleteMultiple(['foo', 'baz']));

        self::assertFalse($this->cache->has('foo'));
        self::assertTrue($this->cache->has('bar'));
        self::assertFalse($this->cache->has('baz'));
    }

    public function test_cannot_delete_cache_files_returns_false(): void
    {
        $this->cache->setMultiple([
            'foo' => 'foo',
            'bar' => 'bar',
        ]);

        $this->files->chmod(0444);

        self::assertFalse($this->cache->deleteMultiple(['foo', 'bar']));
    }

    public function test_clear_cache_does_not_delete_unrelated_files(): void
    {
        (vfsStream::newFile('some-unrelated-file.php'))->withContent('foo')->at($this->files);

        $this->cache->set('foo', 'foo');
        $this->cache->clear();

        self::assertCount(2, $this->files->getChildren());
        self::assertTrue($this->files->hasChild('some-unrelated-file.php'));
    }

    public function test_corrupted_file_throws_exception(): void
    {
        $this->cache->set('foo', 'foo');

        $file = $this->currentCacheFile();
        $file->setContent('<?php invalid php code');

        $this->expectException(CorruptedCompiledPhpCacheFile::class);
        $this->expectExceptionCode(1628949607);
        $this->expectExceptionMessage("Compiled php cache file `{$file->url()}` has corrupted value.");

        $this->cache->get('foo');
    }

    public function test_invalid_cache_entry_type_throws_exception(): void
    {
        $this->cache->set('foo', 'foo');

        $file = $this->currentCacheFile();
        $file->setContent('<?php return 1;');

        $this->expectException(CorruptedCompiledPhpCacheFile::class);
        $this->expectExceptionCode(1628949607);
        $this->expectExceptionMessage("Compiled php cache file `{$file->url()}` has corrupted value.");

        $this->cache->get('foo');
    }

    public function test_cache_directory_not_writable_throws_exception(): void
    {
        $this->expectException(CacheDirectoryNotWritable::class);
        $this->expectExceptionCode(1616445016);
        $this->expectExceptionMessage("Provided directory `{$this->files->url()}` is not writable.");

        $this->files->chmod(0444);

        $this->cache->set('foo', 'foo');
    }

    public function test_temporary_cache_file_not_writable_throws_exception(): void
    {
        $this->expectException(CompiledPhpCacheFileNotWritten::class);
        $this->expectExceptionCode(1616445695);
        $this->expectExceptionMessageMatches('/^File `[^`]+.valinor.tmp[^`]+` could not be written\.$/');

        (vfsStream::newDirectory('.valinor.tmp'))
            ->chmod(0444)
            ->at($this->files);

        $this->cache->set('foo', 'foo');
    }

    public function test_cache_file_not_writable_throws_exception(): void
    {
        $this->expectException(CompiledPhpCacheFileNotWritten::class);
        $this->expectExceptionCode(1616445695);
        $this->expectExceptionMessageMatches('/^File `[^`]+` could not be written\.$/');

        (vfsStream::newDirectory('.valinor.tmp'))->at($this->files);

        $this->files->chmod(0444);

        $this->cache->set('foo', 'foo');
    }

    public function test_temporary_cache_file_is_always_deleted(): void
    {
        $tmpDirectory = vfsStream::newDirectory('.valinor.tmp');
        $tmpDirectory->at($this->files);

        $this->files->chmod(0444);

        try {
            $this->cache->set('foo', 'foo');
        } catch (CompiledPhpCacheFileNotWritten) {
        }

        self::assertEmpty($tmpDirectory->getChildren());
    }

    private function currentCacheFile(): vfsStreamFile
    {
        foreach ($this->files->getChildren() as $file) {
            if ($file instanceof vfsStreamFile && str_ends_with($file->getName(), 'php')) {
                return $file;
            }
        }

        throw new RuntimeException('Cache file not found.');
    }
}
