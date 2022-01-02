<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Cache\Compiled;

use CuyZ\Valinor\Cache\Compiled\CompiledPhpFileCache;
use CuyZ\Valinor\Cache\Exception\CorruptedCompiledPhpCacheFile;
use CuyZ\Valinor\Tests\Fake\Cache\Compiled\FakeCacheCompiler;
use CuyZ\Valinor\Tests\Fake\Cache\Compiled\FakeCacheValidationCompiler;
use DateTime;
use FilesystemIterator;
use PHPUnit\Framework\TestCase;
use SplFileInfo;

use function file_put_contents;
use function glob;
use function is_dir;
use function iterator_to_array;
use function mkdir;
use function rmdir;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;

final class CompiledPhpFileCacheTest extends TestCase
{
    private string $cacheDir;

    /** @var CompiledPhpFileCache<mixed> */
    private CompiledPhpFileCache $cache;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cacheDir = sys_get_temp_dir();
        $this->cacheDir .= DIRECTORY_SEPARATOR . 'compiled-php-cache';
        $this->cacheDir .= DIRECTORY_SEPARATOR . uniqid('', true);

        $this->cache = new CompiledPhpFileCache($this->cacheDir, new FakeCacheCompiler());
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        if (! is_dir($this->cacheDir)) {
            return;
        }

        /** @var FilesystemIterator $file */
        foreach (new FilesystemIterator($this->cacheDir) as $file) {
            /** @var string $path */
            $path = $file->getRealPath();

            unlink($path);
        }

        rmdir($this->cacheDir);
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

    public function test_clear_caches_clears_all_entries(): void
    {
        $this->cache->set('foo', 'foo');
        $this->cache->set('bar', 'bar');

        $cleared = $this->cache->clear();

        self::assertTrue($cleared);
        self::assertFalse($this->cache->has('foo'));
        self::assertFalse($this->cache->has('bar'));
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

    public function test_failing_validation_compilation_invalidates_cache_entry(): void
    {
        $compiler = new FakeCacheValidationCompiler();
        $compiler->compileValidation = false;

        $cache = new CompiledPhpFileCache($this->cacheDir, $compiler);
        $cache->set('foo', 'foo');

        self::assertFalse($cache->has('foo'));
    }

    public function test_clear_cache_does_not_delete_unrelated_files(): void
    {
        if (! is_dir($this->cacheDir)) {
            mkdir($this->cacheDir);
        }

        touch($filenameA = $this->cacheDir . DIRECTORY_SEPARATOR . 'foo.php');
        touch($filenameB = $this->cacheDir . DIRECTORY_SEPARATOR . 'bar.php');

        $this->cache->set('foo', 'foo');
        $this->cache->clear();

        self::assertFileExists($filenameA);
        self::assertFileExists($filenameB);
        self::assertCount(2, glob($this->cacheDir . DIRECTORY_SEPARATOR . '*')); // @phpstan-ignore-line

        unlink($filenameA);
        unlink($filenameB);
    }

    public function test_corrupted_file_throws_exception(): void
    {
        $this->cache->set('foo', 'foo');

        /** @var SplFileInfo $file */
        $file = (new FilesystemIterator($this->cacheDir))->current();

        /** @var string $filename */
        $filename = $file->getPathname();

        file_put_contents($filename, '<?php throw new Exception()');

        $this->expectException(CorruptedCompiledPhpCacheFile::class);
        $this->expectExceptionCode(1628949607);
        $this->expectExceptionMessage("Compiled php cache file `$filename` has corrupted value.");

        $this->cache->get('foo');

        unlink($filename);
    }

    public function test_invalid_cache_entry_type_throws_exception(): void
    {
        $this->cache->set('foo', 'foo');

        /** @var SplFileInfo $file */
        $file = (new FilesystemIterator($this->cacheDir))->current();

        /** @var string $filename */
        $filename = $file->getPathname();

        file_put_contents($filename, '<?php return 1;');

        $this->expectException(CorruptedCompiledPhpCacheFile::class);
        $this->expectExceptionCode(1628949607);
        $this->expectExceptionMessage("Compiled php cache file `$filename` has corrupted value.");

        $this->cache->get('foo');

        unlink($filename);
    }
}
