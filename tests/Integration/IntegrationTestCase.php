<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration;

use CuyZ\Valinor\Cache\FileSystemCache;
use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Tree\Message\Messages;
use CuyZ\Valinor\Mapper\Tree\Node;
use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Tests\Integration\Mapping\Namespace\NamespacedInterfaceInferringTest;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;

use function array_keys;
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

        // If we are already running with a cache, avoid an endless loop by bailing out now.
        if (isset($this->cacheToInject)) {
            return;
        }

        $cacheDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . bin2hex(random_bytes(16));
        $this->cacheToInject = new FileSystemCache($cacheDir);

        // First rerun of the test: the cache entries will be injected.
        parent::runBare();

        // Second rerun of the test: the cache entries will be used and tested.
        parent::runBare();

        $this->cacheToInject->clear();
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

    /**
     * @param non-empty-array<non-empty-string> $expected
     */
    protected function assertMappingErrors(MappingError $error, array $expected): void
    {
        $errors = [];

        foreach (Messages::flattenFromNode($error->node()) as $message) {
            $errors[$message->node()->path()] = $message;
        }

        $remainingErrors = $errors;

        foreach ($expected as $path => $message) {
            self::assertArrayHasKey($path, $remainingErrors, "Error path `$path` not found in error messages, the following path(s) were found: `" . implode('`, `', array_keys($errors)) . '`.');

            if (! preg_match('/^\[([^]]+)] (.*)/', $message, $matches)) {
                self::fail('Incorrect error message format. Expected format: `[code] message`.');
            }

            self::assertSame($matches[2], $remainingErrors[$path]->toString());
            self::assertSame($matches[1], $remainingErrors[$path]->code());

            unset($remainingErrors[$path]);
        }

        if ($remainingErrors !== []) {
            self::fail('Untested error messages at path(s): `' . implode('`, `', array_keys($remainingErrors)) . '`');
        }
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
