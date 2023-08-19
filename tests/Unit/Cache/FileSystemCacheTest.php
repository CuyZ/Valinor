<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Cache;

use CuyZ\Valinor\Cache\FileSystemCache;
use CuyZ\Valinor\Definition\ClassDefinition;
use CuyZ\Valinor\Definition\FunctionDefinition;
use CuyZ\Valinor\Tests\Fake\Cache\FakeCache;
use CuyZ\Valinor\Tests\Fake\Cache\FakeCacheWithWarmup;
use CuyZ\Valinor\Tests\Fake\Cache\FakeFailingCache;
use CuyZ\Valinor\Tests\Fake\Definition\FakeClassDefinition;
use CuyZ\Valinor\Tests\Fake\Definition\FakeFunctionDefinition;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;

use function iterator_to_array;

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

        $this->injectFakeCache([
            '*' => new FakeCache(),
            ClassDefinition::class => new FakeCache(),
            FunctionDefinition::class => new FakeCache(),
        ]);
    }

    public function test_cache_warmup_calls_delegates_warmup(): void
    {
        $cacheWithWarmup = new FakeCacheWithWarmup();

        $this->injectFakeCache([
            '*' => new FakeCache(),
            ClassDefinition::class => $cacheWithWarmup,
            FunctionDefinition::class => new FakeCache(),
        ]);

        $this->cache->warmup();

        self::assertSame(1, $cacheWithWarmup->timesWarmupWasCalled());
    }

    public function test_cache_entries_are_handled_properly(): void
    {
        $classDefinition = FakeClassDefinition::new();
        $functionDefinition = FakeFunctionDefinition::new();

        self::assertFalse($this->cache->has('class-definition'));
        self::assertFalse($this->cache->has('function-definition'));

        self::assertTrue($this->cache->set('class-definition', $classDefinition));
        self::assertTrue($this->cache->set('function-definition', $functionDefinition));

        self::assertTrue($this->cache->has('class-definition'));
        self::assertTrue($this->cache->has('function-definition'));

        /** @var ClassDefinition $cachedClassDefinition */
        $cachedClassDefinition = $this->cache->get('class-definition');
        /** @var FunctionDefinition $cachedFunctionDefinition */
        $cachedFunctionDefinition = $this->cache->get('function-definition');

        self::assertSame($classDefinition->name(), $cachedClassDefinition->name());
        self::assertSame($functionDefinition->signature(), $cachedFunctionDefinition->signature());

        self::assertTrue($this->cache->delete('class-definition'));
        self::assertTrue($this->cache->delete('function-definition'));

        self::assertFalse($this->cache->has('class-definition'));
        self::assertFalse($this->cache->has('function-definition'));
    }

    public function test_clear_cache_clears_all_caches(): void
    {
        $classDefinition = FakeClassDefinition::new();
        $functionDefinition = FakeFunctionDefinition::new();

        $this->cache->set('class-definition', $classDefinition);
        $this->cache->set('function-definition', $functionDefinition);

        self::assertTrue($this->cache->has('class-definition'));
        self::assertTrue($this->cache->has('function-definition'));

        self::assertTrue($this->cache->clear());

        self::assertFalse($this->cache->has('class-definition'));
        self::assertFalse($this->cache->has('function-definition'));
    }

    public function test_multiple_cache_entries_are_handled_properly(): void
    {
        $classDefinition = FakeClassDefinition::new();
        $functionDefinition = FakeFunctionDefinition::new();

        self::assertTrue($this->cache->setMultiple([
            'class-definition' => $classDefinition,
            'function-definition' => $functionDefinition,
        ]));

        $cached = iterator_to_array($this->cache->getMultiple(['class-definition', 'function-definition']));

        /** @var ClassDefinition $cachedClassDefinition */
        $cachedClassDefinition = $cached['class-definition'];
        /** @var FunctionDefinition $cachedFunctionDefinition */
        $cachedFunctionDefinition = $cached['function-definition'];

        self::assertSame($classDefinition->name(), $cachedClassDefinition->name());
        self::assertSame($functionDefinition->signature(), $cachedFunctionDefinition->signature());

        self::assertTrue($this->cache->deleteMultiple(['class-definition', 'function-definition']));

        self::assertFalse($this->cache->has('class-definition'));
        self::assertFalse($this->cache->has('function-definition'));
    }

    public function test_methods_returns_false_if_delegates_fail(): void
    {
        $this->injectFakeCache([
            '*' => new FakeCache(),
            ClassDefinition::class => new FakeCache(),
            FunctionDefinition::class => new FakeFailingCache(),
        ]);

        $classDefinition = FakeClassDefinition::new();
        $functionDefinition = FakeFunctionDefinition::new();

        self::assertTrue($this->cache->set('class-definition', $classDefinition));
        self::assertFalse($this->cache->set('function-definition', $functionDefinition));

        self::assertFalse($this->cache->delete('class-definition'));
        self::assertFalse($this->cache->delete('function-definition'));

        self::assertFalse($this->cache->clear());

        self::assertFalse($this->cache->setMultiple([
            'class-definition' => $classDefinition,
            'function-definition' => $functionDefinition,
        ]));

        self::assertFalse($this->cache->deleteMultiple(['class-definition', 'function-definition']));
    }

    public function test_get_non_existing_entry_returns_default_value(): void
    {
        $defaultValue = FakeClassDefinition::new();

        self::assertSame($defaultValue, $this->cache->get('non-existing-entry', $defaultValue));
    }

    /**
     * @param array<string, CacheInterface<mixed>> $caches
     */
    private function injectFakeCache(array $caches): void
    {
        (function () use ($caches) {
            $this->delegates = $caches;
        })->call($this->cache);
    }
}
