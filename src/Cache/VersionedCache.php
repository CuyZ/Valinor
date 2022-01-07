<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Cache;

use CuyZ\Valinor\Utility\Package;
use Psr\SimpleCache\CacheInterface;
use Traversable;

/**
 * @internal
 *
 * @template EntryType
 * @implements CacheInterface<EntryType>
 */
final class VersionedCache implements CacheInterface
{
    /** @var CacheInterface<EntryType>  */
    private CacheInterface $delegate;

    private string $version;

    /**
     * @param CacheInterface<EntryType> $delegate
     */
    public function __construct(CacheInterface $delegate)
    {
        $this->delegate = $delegate;
        /** @infection-ignore-all */
        $this->version = PHP_VERSION . '/' . Package::version();
    }

    public function get($key, $default = null)
    {
        return $this->delegate->get($this->key($key), $default);
    }

    public function set($key, $value, $ttl = null): bool
    {
        return $this->delegate->set($this->key($key), $value, $ttl);
    }

    public function delete($key): bool
    {
        return $this->delegate->delete($this->key($key));
    }

    public function clear(): bool
    {
        return $this->delegate->clear();
    }

    public function has($key): bool
    {
        return $this->delegate->has($this->key($key));
    }

    /**
     * @return Traversable<string, EntryType>
     */
    public function getMultiple($keys, $default = null): Traversable
    {
        foreach ($keys as $key) {
            yield $key => $this->delegate->get($this->key($key), $default);
        }
    }

    public function setMultiple($values, $ttl = null): bool
    {
        $versionedValues = [];

        foreach ($values as $key => $value) {
            $versionedValues[$this->key($key)] = $value;
        }

        return $this->delegate->setMultiple($versionedValues, $ttl);
    }

    public function deleteMultiple($keys): bool
    {
        $transformedKeys = [];

        foreach ($keys as $key) {
            $transformedKeys[] = $this->key($key);
        }

        return $this->delegate->deleteMultiple($transformedKeys);
    }

    private function key(string $key): string
    {
        return "$key.$this->version";
    }
}
