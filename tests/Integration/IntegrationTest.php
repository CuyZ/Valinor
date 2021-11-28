<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\MapperBuilder;
use FilesystemIterator;
use PHPUnit\Framework\TestCase;
use Throwable;

use function array_map;
use function implode;

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
        $errors = [];

        foreach ($error->describe() as $path => $messages) {
            $list = array_map(
                static fn (Throwable $message) => $message->getMessage(),
                $messages
            );

            $errors[] = "$path: " . implode(' / ', $list);
        }

        self::fail(implode(' — ', $errors));
    }
}
