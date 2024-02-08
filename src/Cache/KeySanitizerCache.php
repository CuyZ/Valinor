<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Cache;

use Closure;
use CuyZ\Valinor\Utility\Package;
use Psr\SimpleCache\CacheInterface;
use Traversable;

use function sha1;

/**
 * @internal
 *
 * @template EntryType
 * @implements WarmupCache<EntryType>
 */
final class KeySanitizerCache implements WarmupCache
{
    private static string $version;

    /** @var Closure(string): string */
    private Closure $sanitize;

    public function __construct(
        /** @var CacheInterface<EntryType> */
        private CacheInterface $delegate
    ) {
        // Two things:
        // 1. We append the current version of the package to the cache key in
        //    order to avoid collisions between entries from different versions
        //    of the library.
        // 2. The key is sha1'd so that it does not contain illegal characters.
        //    @see https://www.php-fig.org/psr/psr-16/#12-definitions
        // @infection-ignore-all
        $this->sanitize = static fn (string $key) => $key . sha1(self::$version ??= PHP_VERSION . '/' . Package::version());
    }

    public function warmup(): void
    {
        if ($this->delegate instanceof WarmupCache) {
            $this->delegate->warmup();
        }
    }

    public function get($key, $default = null): mixed
    {
        return $this->delegate->get(($this->sanitize)($key), $default);
    }

    public function set($key, $value, $ttl = null): bool
    {
        return $this->delegate->set(($this->sanitize)($key), $value, $ttl);
    }

    public function delete($key): bool
    {
        return $this->delegate->delete(($this->sanitize)($key));
    }

    public function clear(): bool
    {
        return $this->delegate->clear();
    }

    public function has($key): bool
    {
        return $this->delegate->has(($this->sanitize)($key));
    }

    /**
     * @return Traversable<string, EntryType|null>
     */
    public function getMultiple($keys, $default = null): Traversable
    {
        foreach ($keys as $key) {
            yield $key => $this->delegate->get(($this->sanitize)($key), $default);
        }
    }

    public function setMultiple($values, $ttl = null): bool
    {
        $versionedValues = [];

        foreach ($values as $key => $value) {
            $versionedValues[($this->sanitize)($key)] = $value;
        }

        return $this->delegate->setMultiple($versionedValues, $ttl);
    }

    public function deleteMultiple($keys): bool
    {
        $transformedKeys = [];

        foreach ($keys as $key) {
            $transformedKeys[] = ($this->sanitize)($key);
        }

        return $this->delegate->deleteMultiple($transformedKeys);
    }
}
