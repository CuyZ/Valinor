<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Cache;

use CuyZ\Valinor\Cache\FileSystemCache;
use CuyZ\Valinor\Cache\FileWatchingCache;
use CuyZ\Valinor\Mapper\TreeMapper;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;
use CuyZ\Valinor\Tests\Integration\Mapping\Fixture\SimpleObject;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamFile;
use Psr\SimpleCache\CacheInterface;
use stdClass;

use function file_get_contents;
use function str_ends_with;

final class CacheInjectionTest extends IntegrationTestCase
{
    public function test_cache_entries_are_written_once_during_mapping(): void
    {
        $cacheDirectory = vfsStream::setup('cache-dir');

        $cache = new FileSystemCache($cacheDirectory->url());
        $cache = new FileWatchingCache($cache);

        // Calling the mapper a first time to populate the cache entries…
        $this->createMapper($cache)->map(SimpleObject::class, 'foo');

        $files = $this->recursivelyFindPhpFiles($cacheDirectory);

        self::assertCount(6, $files);

        foreach ($files as $file) {
            $file->setContent($file->getContent() . "\n// generated value 1661895014");
        }

        // Calling the mapper a second time: checking that the cache entries
        // have not been overridden.
        $this->createMapper($cache)->map(SimpleObject::class, 'foo');

        foreach ($files as $file) {
            self::assertStringContainsString('// generated value 1661895014', file_get_contents($file->url()) ?: '');
        }
    }

    /**
     * @param CacheInterface<mixed> $cache
     */
    private function createMapper(CacheInterface $cache): TreeMapper
    {
        return $this->mapperBuilder()
            ->withCache($cache)
            // The cache should be able to cache function definitions…
            ->registerConstructor(fn (): stdClass => new stdClass())
            ->mapper();
    }

    /**
     * @return vfsStreamFile[]
     */
    private function recursivelyFindPhpFiles(vfsStreamDirectory $directory): array
    {
        $files = [];

        foreach ($directory->getChildren() as $child) {
            if ($child instanceof vfsStreamFile && str_ends_with($child->getName(), '.php')) {
                $files[] = $child;
            }

            if ($child instanceof vfsStreamDirectory) {
                $files = [...$files, ...$this->recursivelyFindPhpFiles($child)];
            }
        }

        return $files;
    }
}
