<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Cache;

use CuyZ\Valinor\Definition\ClassDefinition;
use CuyZ\Valinor\Definition\FunctionDefinition;
use CuyZ\Valinor\Utility\Reflection\Reflection;
use Psr\SimpleCache\CacheInterface;

use function file_exists;
use function filemtime;
use function is_string;

/**
 * This cache implementation will watch the files of the application and
 * invalidate cache entries when a PHP file is modified — preventing the library
 * not behaving as expected when the signature of a property or a method
 * changes.
 *
 * This is especially useful when the application runs in a development
 * environment, where source files are often modified by developers.
 *
 * It should decorate the original cache implementation and should be given to
 * the mapper builder: @see \CuyZ\Valinor\MapperBuilder::withCache
 *
 * @api
 *
 * @phpstan-type TimestampsArray = array<string, int>
 * @template EntryType
 * @implements WarmupCache<EntryType|TimestampsArray>
 */
final class FileWatchingCache implements WarmupCache
{
    /** @var array<string, TimestampsArray> */
    private array $timestamps = [];

    public function __construct(
        /** @var CacheInterface<EntryType|TimestampsArray> */
        private CacheInterface $delegate
    ) {}

    public function warmup(): void
    {
        if ($this->delegate instanceof WarmupCache) {
            $this->delegate->warmup();
        }
    }

    public function has($key): bool
    {
        foreach ($this->timestamps($key) as $fileName => $timestamp) {
            if (! file_exists($fileName)) {
                return false;
            }

            if (filemtime($fileName) !== $timestamp) {
                return false;
            }
        }

        return $this->delegate->has($key);
    }

    public function get($key, $default = null): mixed
    {
        if (! $this->has($key)) {
            return $default;
        }

        return $this->delegate->get($key, $default);
    }

    public function set($key, $value, $ttl = null): bool
    {
        $this->saveTimestamps($key, $value);

        return $this->delegate->set($key, $value, $ttl);
    }

    public function delete($key): bool
    {
        return $this->delegate->delete($key);
    }

    public function clear(): bool
    {
        $this->timestamps = [];

        return $this->delegate->clear();
    }

    public function getMultiple($keys, $default = null): iterable
    {
        return $this->delegate->getMultiple($keys, $default);
    }

    public function setMultiple($values, $ttl = null): bool
    {
        foreach ($values as $key => $value) {
            $this->saveTimestamps($key, $value);
        }

        return $this->delegate->setMultiple($values, $ttl);
    }

    public function deleteMultiple($keys): bool
    {
        return $this->delegate->deleteMultiple($keys);
    }

    /**
     * @return TimestampsArray
     */
    private function timestamps(string $key): array
    {
        return $this->timestamps[$key] ??= $this->delegate->get("$key.timestamps", []); // @phpstan-ignore-line
    }

    private function saveTimestamps(string $key, mixed $value): void
    {
        $this->timestamps[$key] = [];

        $fileNames = [];

        if ($value instanceof ClassDefinition) {
            $reflection = Reflection::class($value->name);

            do {
                $fileNames[] = $reflection->getFileName();
            } while ($reflection = $reflection->getParentClass());
        }

        if ($value instanceof FunctionDefinition) {
            $fileNames[] = $value->fileName;
        }

        foreach ($fileNames as $fileName) {
            if (! is_string($fileName)) {
                // @infection-ignore-all
                continue;
            }

            $time = @filemtime($fileName);

            // @infection-ignore-all
            if (false === $time) {
                continue;
            }

            $this->timestamps[$key][$fileName] = $time;
        }

        if (! empty($this->timestamps[$key])) {
            $this->delegate->set("$key.timestamps", $this->timestamps[$key]);
        }
    }
}
