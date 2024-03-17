<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Cache;

use Closure;
use Psr\SimpleCache\CacheInterface;
use Traversable;

/**
 * @internal
 *
 * @template EntryType
 * @implements WarmupCache<EntryType>
 */
final class KeySanitizerCache implements WarmupCache
{
    private string $version;

    public function __construct(
        /** @var CacheInterface<EntryType> */
        private CacheInterface $delegate,
        /** @var Closure(): string */
        private Closure $sanitizeCallback,
    ) {}

    public function warmup(): void
    {
        if ($this->delegate instanceof WarmupCache) {
            $this->delegate->warmup();
        }
    }

    public function get($key, $default = null): mixed
    {
        return $this->delegate->get($this->sanitize($key), $default);
    }

    public function set($key, $value, $ttl = null): bool
    {
        return $this->delegate->set($this->sanitize($key), $value, $ttl);
    }

    public function delete($key): bool
    {
        return $this->delegate->delete($this->sanitize($key));
    }

    public function clear(): bool
    {
        return $this->delegate->clear();
    }

    public function has($key): bool
    {
        return $this->delegate->has($this->sanitize($key));
    }

    /**
     * @return Traversable<string, EntryType|null>
     */
    public function getMultiple($keys, $default = null): Traversable
    {
        foreach ($keys as $key) {
            yield $key => $this->delegate->get($this->sanitize($key), $default);
        }
    }

    public function setMultiple($values, $ttl = null): bool
    {
        $versionedValues = [];

        foreach ($values as $key => $value) {
            $versionedValues[$this->sanitize($key)] = $value;
        }

        return $this->delegate->setMultiple($versionedValues, $ttl);
    }

    public function deleteMultiple($keys): bool
    {
        $transformedKeys = [];

        foreach ($keys as $key) {
            $transformedKeys[] = $this->sanitize($key);
        }

        return $this->delegate->deleteMultiple($transformedKeys);
    }

    private function sanitize(string $key): string
    {
        return $key . $this->version ??= ($this->sanitizeCallback)();
    }
}
