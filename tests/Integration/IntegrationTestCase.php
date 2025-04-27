<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration;

use CuyZ\Valinor\Cache\Cache;
use CuyZ\Valinor\Cache\FileSystemCache;
use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Tree\Message\DefaultMessage;
use CuyZ\Valinor\Mapper\Tree\Message\NodeMessage;
use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Tests\Integration\Mapping\Namespace\NamespacedInterfaceInferringTest;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

use function array_keys;
use function array_map;
use function bin2hex;
use function implode;
use function preg_match;
use function random_bytes;
use function sys_get_temp_dir;

abstract class IntegrationTestCase extends TestCase
{
    private Cache $cacheToInject;

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

        if (! $this->status()->isSuccess()) {
            return;
        }

        // First rerun of the test: the cache entries will be injected.
        parent::runBare();

        // Second rerun of the test: the cache entries will be used and tested.
        parent::runBare();

        (new Filesystem())->remove($cacheDir);
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
    protected function assertMappingErrors(MappingError $exception, array $expected, bool $assertErrorsBodiesAreRegistered = true): void
    {
        if ($assertErrorsBodiesAreRegistered) {
            $this->assertErrorsBodiesAreRegistered($exception);
        }

        $errors = [];

        foreach ($exception->messages() as $message) {
            $errors[$message->path()] = $message;
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
        $this->assertErrorsBodiesAreRegistered($error);

        $errors = array_map(
            fn (NodeMessage $message) => "{$message->path()}: {$message->toString()} ({$message->code()})",
            $error->messages()->toArray()
        );

        self::fail(implode(' — ', $errors));
    }

    private function assertErrorsBodiesAreRegistered(MappingError $error): void
    {
        foreach ($error->messages() as $message) {
            self::assertArrayHasKey(
                $message->body(),
                DefaultMessage::TRANSLATIONS,
                'The error message is not registered in `' . DefaultMessage::class . '::TRANSLATIONS`.',
            );
        }
    }
}
