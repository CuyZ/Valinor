<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Cache\Compiled;

use CuyZ\Valinor\Cache\FileWatchingCache;
use CuyZ\Valinor\Tests\Fake\Cache\FakeCache;
use CuyZ\Valinor\Tests\Fake\Cache\FakeCacheWithWarmup;
use CuyZ\Valinor\Tests\Fake\Definition\FakeClassDefinition;
use CuyZ\Valinor\Tests\Fake\Definition\FakeFunctionDefinition;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamContent;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

use function iterator_to_array;

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

    public function test_cache_warmup_calls_delegate_warmup(): void
    {
        $delegate = new FakeCacheWithWarmup();
        $cache = new FileWatchingCache($delegate);

        $cache->warmup();

        self::assertSame(1, $delegate->timesWarmupWasCalled());
    }

    /**
     * @doesNotPerformAssertions
     */
    public function test_cache_warmup_does_not_call_delegate_warmup_if_not_handled(): void
    {
        $delegate = new FakeCache();
        $cache = new FileWatchingCache($delegate);

        $cache->warmup();
    }

    public function test_value_can_be_fetched_and_deleted(): void
    {
        $key = 'foo';
        $value = new stdClass();

        self::assertFalse($this->cache->has($key));
        self::assertTrue($this->cache->set($key, $value));
        self::assertTrue($this->cache->has($key));
        self::assertSame($value, $this->cache->get($key));
        self::assertTrue($this->cache->delete($key));
        self::assertFalse($this->cache->has($key));
    }

    public function test_get_non_existing_entry_returns_default_value(): void
    {
        $defaultValue = new stdClass();

        self::assertSame($defaultValue, $this->cache->get('Schwifty', $defaultValue));
    }

    public function test_get_existing_entry_does_not_return_default_value(): void
    {
        $this->cache->set('foo', 'foo');

        self::assertSame('foo', $this->cache->get('foo', 'bar'));
    }

    public function test_clear_entries_clears_everything(): void
    {
        $keyA = 'foo';
        $keyB = 'bar';

        $this->cache->set($keyA, new stdClass());
        $this->cache->set($keyB, new stdClass());

        self::assertTrue($this->cache->has($keyA));
        self::assertTrue($this->cache->has($keyB));
        self::assertTrue($this->cache->clear());
        self::assertFalse($this->cache->has($keyA));
        self::assertFalse($this->cache->has($keyB));
    }

    public function test_multiple_values_set_can_be_fetched_and_deleted(): void
    {
        $values = [
            'foo' => new stdClass(),
            'bar' => new stdClass(),
        ];

        self::assertFalse($this->cache->has('foo'));
        self::assertFalse($this->cache->has('bar'));

        self::assertTrue($this->cache->setMultiple($values));

        self::assertTrue($this->cache->has('foo'));
        self::assertTrue($this->cache->has('bar'));

        // PHP8.1 array unpacking
        self::assertEquals($values, iterator_to_array($this->cache->getMultiple(['foo', 'bar']))); // @phpstan-ignore-line

        self::assertTrue($this->cache->deleteMultiple(['foo', 'bar']));

        self::assertFalse($this->cache->has('foo'));
        self::assertFalse($this->cache->has('bar'));
    }

    public function test_set_php_internal_class_definition_saves_cache_entry(): void
    {
        $this->cache->set('some-class-definition', FakeClassDefinition::new(stdClass::class));

        self::assertTrue($this->cache->has('some-class-definition'));
    }

    public function test_modifying_class_definition_file_invalids_cache(): void
    {
        $fileA = (vfsStream::newFile('ObjectA.php'))
            ->withContent('<?php class ObjectA {}')
            ->at($this->files);

        $fileB = (vfsStream::newFile('ObjectB.php'))
            ->withContent('<?php class ObjectB extends ObjectA {}')
            ->at($this->files);

        include $fileA->url();
        include $fileB->url();

        $class = FakeClassDefinition::fromReflection(new ReflectionClass('ObjectB')); // @phpstan-ignore-line

        self::assertTrue($this->cache->set('some-class-definition', $class));
        self::assertTrue($this->cache->has('some-class-definition'));

        unlink($fileA->url());
        $fileA->lastModified(time() + 5)->at($this->files);

        self::assertFalse($this->cache->has('some-class-definition'));
        self::assertTrue($this->cache->setMultiple(['some-class-definition' => $class]));
        self::assertTrue($this->cache->has('some-class-definition'));

        unlink($fileB->url());

        self::assertFalse($this->cache->has('some-class-definition'));
    }

    public function test_modifying_function_definition_file_invalids_cache(): void
    {
        $file = $this->functionDefinitionFile();

        $function = FakeFunctionDefinition::new($file->url());

        self::assertTrue($this->cache->set('some-function-definition', $function));
        self::assertTrue($this->cache->has('some-function-definition'));

        unlink($file->url());
        $file->lastModified(time() + 5)->at($this->files);

        self::assertFalse($this->cache->has('some-function-definition'));
        self::assertTrue($this->cache->setMultiple(['some-function-definition' => $function]));
        self::assertTrue($this->cache->has('some-function-definition'));

        unlink($file->url());
        $file->lastModified(time() + 10)->at($this->files);

        self::assertFalse($this->cache->has('some-function-definition'));
    }

    public function test_file_timestamps_are_fetched_once_per_request(): void
    {
        $cacheA = new FileWatchingCache($this->delegateCache);
        $cacheB = new FileWatchingCache($this->delegateCache);

        self::assertFalse($cacheA->has('some-function-definition'));
        self::assertFalse($cacheA->has('some-function-definition'));
        self::assertFalse($cacheB->has('some-function-definition'));
        self::assertFalse($cacheB->has('some-function-definition'));

        $file = $this->functionDefinitionFile()->url();

        $cacheA->set('some-function-definition', FakeFunctionDefinition::new($file));
        $cacheB->set('some-function-definition', FakeFunctionDefinition::new($file));

        self::assertSame(2, $this->delegateCache->timesEntryWasSet('some-function-definition.timestamps'));
        self::assertSame(2, $this->delegateCache->timesEntryWasFetched('some-function-definition.timestamps'));
    }

    private function functionDefinitionFile(): vfsStreamContent
    {
        return (vfsStream::newFile('_function_definition_file.php'))
            ->withContent('<?php function _valinor_test_function() {}')
            ->at($this->files);
    }
}
