<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Tree\Node;
use CuyZ\Valinor\MapperBuilder;
use FilesystemIterator;
use PHPUnit\Framework\TestCase;
use Throwable;

use function implode;
use function iterator_to_array;

abstract class IntegrationTest extends TestCase
{
    protected MapperBuilder $mapperBuilder;

    private string $cacheDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cacheDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'valinor-integration-test' . DIRECTORY_SEPARATOR . uniqid();
        $this->mapperBuilder = (new MapperBuilder())->withCacheDir($this->cacheDir);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        /** @var FilesystemIterator $file */
        foreach (new FilesystemIterator($this->cacheDir) as $file) {
            /** @var string $path */
            $path = $file->getRealPath();

            unlink($path);
        }

        rmdir($this->cacheDir);
    }

    /**
     * @return never-return
     */
    protected function mappingFail(MappingError $error)
    {
        $errorFinder = static function (Node $node, callable $errorFinder) {
            if ($node->isValid()) {
                return;
            }

            $errors = [];

            foreach ($node->messages() as $message) {
                if ($message instanceof Throwable) {
                    $errors[] = $message->getMessage();
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
