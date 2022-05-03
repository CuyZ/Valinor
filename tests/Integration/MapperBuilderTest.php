<?php
declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration;

use CuyZ\Valinor\Cache\Compiled\CompiledPhpFileCache;
use CuyZ\Valinor\Tests\Fixture\Object\ObjectWithAttributes;
use CuyZ\Valinor\Tests\Fixture\Object\ObjectWithNestedAttributes;
use CuyZ\Valinor\Tests\Fixture\Object\ObjectWithParameterDefaultObjectValue;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamContent;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamFile;

final class MapperBuilderTest extends IntegrationTest
{
    private vfsStreamDirectory $cacheDirectoryRoot;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cacheDirectoryRoot = vfsStream::setup('valinor');
    }

    public function test_will_warmup_type_parser_cache(): void
    {
        self::assertEmpty(array_filter(
            iterator_to_array($this->cacheDirectoryRoot),
            $this->cacheFileFilterCallback()
        ));

        $builder = $this->mapperBuilder->withCacheDir($this->cacheDirectoryRoot->url());
        $builder->warmup(ObjectWithAttributes::class);
        self::assertTrue($this->cacheDirectoryRoot->hasChild(CompiledPhpFileCache::CACHE_DIRECTORY_NAME));
        self::assertCount(1, array_filter(
            iterator_to_array($this->cacheDirectoryRoot),
            $this->cacheFileFilterCallback()
        ));
    }

    public function test_will_warmup_type_parser_cache_with_multiple_signatures(): void
    {
        self::assertEmpty(array_filter(
            iterator_to_array($this->cacheDirectoryRoot),
            $this->cacheFileFilterCallback()
        ));

        $builder = $this->mapperBuilder->withCacheDir($this->cacheDirectoryRoot->url());
        $builder->warmup(ObjectWithAttributes::class, ObjectWithNestedAttributes::class, ObjectWithParameterDefaultObjectValue::class);
        self::assertTrue($this->cacheDirectoryRoot->hasChild(CompiledPhpFileCache::CACHE_DIRECTORY_NAME));
        self::assertCount(3, array_filter(
            iterator_to_array($this->cacheDirectoryRoot),
            $this->cacheFileFilterCallback()
        ));
    }

    /**
     * @return callable(vfsStreamContent):bool
     */
    private function cacheFileFilterCallback(): callable
    {
        return static fn (vfsStreamContent $content): bool => $content instanceof vfsStreamFile;
    }
}
