<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit;

use CuyZ\Valinor\Cache\Compiled\CompiledPhpFileCache;
use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Tests\Fixture\Object\ObjectWithAttributes;
use CuyZ\Valinor\Tests\Fixture\Object\ObjectWithNestedAttributes;
use CuyZ\Valinor\Tests\Fixture\Object\ObjectWithParameterDefaultObjectValue;
use DateTime;
use DateTimeInterface;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamContent;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamFile;
use PHPUnit\Framework\TestCase;
use stdClass;

use function array_filter;
use function iterator_to_array;
use function sys_get_temp_dir;

final class MapperBuilderTest extends TestCase
{
    private MapperBuilder $mapperBuilder;
    private vfsStreamDirectory $cacheDirectoryRoot;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mapperBuilder = new MapperBuilder();
        $this->cacheDirectoryRoot = vfsStream::setup('valinor');
    }

    public function test_builder_methods_return_clone_of_builder_instance(): void
    {
        $builderA = $this->mapperBuilder;
        $builderB = $builderA->infer(DateTimeInterface::class, static fn () => DateTime::class);
        $builderC = $builderA->bind(static fn (): DateTime => new DateTime());
        $builderD = $builderA->registerConstructor(static fn (): stdClass => new stdClass());
        $builderE = $builderA->alter(static fn (string $value): string => 'foo');
        $builderF = $builderA->withCacheDir(sys_get_temp_dir());
        $builderG = $builderA->enableLegacyDoctrineAnnotations();

        self::assertNotSame($builderA, $builderB);
        self::assertNotSame($builderA, $builderC);
        self::assertNotSame($builderA, $builderD);
        self::assertNotSame($builderA, $builderE);
        self::assertNotSame($builderA, $builderF);
        self::assertNotSame($builderA, $builderG);
    }

    public function test_mapper_instance_is_the_same(): void
    {
        self::assertSame($this->mapperBuilder->mapper(), $this->mapperBuilder->mapper());
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
