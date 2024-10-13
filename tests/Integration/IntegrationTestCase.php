<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration;

use CuyZ\Valinor\Cache\FileSystemCache;
use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Tree\Node;
use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Tests\Integration\Mapping\Namespace\NamespacedInterfaceInferringTest;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;

use function bin2hex;
use function implode;
use function iterator_to_array;
use function random_bytes;
use function sys_get_temp_dir;

abstract class IntegrationTestCase extends TestCase
{
    /** @var CacheInterface<mixed> */
    private CacheInterface $cacheToInject;

    /**
     * After the test has run, it is run again with the cache injected. This
     * allows us to test the exact same case and assertions, but with a
     * completely different code path (as the file cache is used only in one
     * scenario).
     */
    protected function tearDown(): void
    {
        // Excluding this test because it cannot run twice in the same process.
        if (static::class === NamespacedInterfaceInferringTest::class) {
            return;
        }

        $cacheDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . bin2hex(random_bytes(16));
        $cacheDir = __DIR__ . '/../../zzzzzzz';
        $this->cacheToInject = new FileSystemCache($cacheDir);

        // First rerun of the test: the cache entries will be injected.
        //        parent::runTest();

        // Second rerun of the test: the cache entries will be used and tested.
        //        parent::runTest();

        //        $this->cacheToInject->clear(); // @todo
    }

    /**
     * This method *must* be used by every integration test in replacement of a
     * direct creation of a MapperBuilder instance. The goal is to ensure that
     * a test is run twice: once without a cache and once with the internal
     * filesystem cache injected.
     */
    protected function mapperBuilder(): MapperBuilder
    {
        $builder = new MapperBuilder();

        if (isset($this->cacheToInject)) {
            $builder = $builder->withCache($this->cacheToInject);
        }

        return $builder;
    }

    protected function mappingFail(MappingError $error): never
    {
        $errorFinder = static function (Node $node, callable $errorFinder) {
            if ($node->isValid()) {
                return;
            }

            $errors = [];

            foreach ($node->messages() as $message) {
                if ($message->isError()) {
                    $errors[] = (string)$message;
                }
            }

            if (count($errors) > 0) {
                yield $node->path() => "{$node->path()}: " . implode(' / ', $errors);
            }

            foreach ($node->children() as $child) {
                yield from $errorFinder($child, $errorFinder);
            }
        };

        $list = iterator_to_array($errorFinder($error->node(), $errorFinder));

        self::fail(implode(' — ', $list));
    }
}
