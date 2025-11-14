<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Cache;

use function assert;
use function file_exists;
use function filemtime;
use function is_array;
use function var_export;

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
 * the mapper builder: {@see \CuyZ\Valinor\MapperBuilder::withCache()}
 *
 * @api
 *
 * @template EntryType
 * @implements Cache<EntryType>
 */
final class FileWatchingCache implements Cache
{
    /** @var array<string, array<string, int>> */
    private array $timestamps = [];

    public function __construct(
        /** @var Cache<EntryType> */
        private Cache $delegate,
    ) {}

    /** @internal */
    public function get(string $key, mixed ...$arguments): mixed
    {
        $this->timestamps[$key] ??= $this->delegate->get("$key.timestamps"); // @phpstan-ignore assign.propertyType

        if ($this->timestamps[$key] === null) {
            return null;
        }

        assert(is_array($this->timestamps[$key]));

        foreach ($this->timestamps[$key] as $fileName => $timestamp) {
            if (! file_exists($fileName)) {
                return null;
            }

            if (filemtime($fileName) !== $timestamp) {
                return null;
            }
        }

        return $this->delegate->get($key, ...$arguments);
    }

    /** @internal */
    public function set(string $key, CacheEntry $entry): void
    {
        $this->delegate->set($key, $entry);

        $this->timestamps[$key] = [];

        foreach ($entry->filesToWatch as $fileName) {
            $time = @filemtime($fileName);

            if (false === $time) {
                continue;
            }

            $this->timestamps[$key][$fileName] = $time;
        }

        $code = 'fn () => ' . var_export($this->timestamps[$key], true);

        $this->delegate->set("$key.timestamps", new CacheEntry($code));
    }

    public function clear(): void
    {
        $this->timestamps = [];

        $this->delegate->clear();
    }
}
