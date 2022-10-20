<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Cache;

use Psr\SimpleCache\CacheInterface;
use Traversable;

/**
 * @internal
 *
 * @template EntryType
 * @implements CacheInterface<EntryType>
 */
final class ChainCache implements CacheInterface
{
    /** @var array<CacheInterface<EntryType>> */
    private array $delegates;

    private int $count;

    /**
     * @param CacheInterface<EntryType> ...$delegates
     */
    public function __construct(CacheInterface ...$delegates)
    {
        $this->delegates = $delegates;
        $this->count = count($delegates);
    }

    public function get($key, $default = null): mixed
    {
        foreach ($this->delegates as $i => $delegate) {
            $value = $delegate->get($key, $default);

            if (null !== $value) {
                while (--$i >= 0) {
                    $this->delegates[$i]->set($key, $value);
                }

                return $value;
            }
        }

        return $default;
    }

    public function set($key, $value, $ttl = null): bool
    {
        $saved = true;
        $i = $this->count;

        while ($i--) {
            $saved = $this->delegates[$i]->set($key, $value, $ttl) && $saved;
        }

        return $saved;
    }

    public function delete($key): bool
    {
        $deleted = true;
        $i = $this->count;

        while ($i--) {
            $deleted = $this->delegates[$i]->delete($key) && $deleted;
        }

        return $deleted;
    }

    public function clear(): bool
    {
        $cleared = true;
        $i = $this->count;

        while ($i--) {
            $cleared = $this->delegates[$i]->clear() && $cleared;
        }

        return $cleared;
    }

    /**
     * @return Traversable<string, EntryType|null>
     */
    public function getMultiple($keys, $default = null): Traversable
    {
        foreach ($keys as $key) {
            yield $key => $this->get($key, $default);
        }
    }

    public function setMultiple($values, $ttl = null): bool
    {
        $saved = true;

        foreach ($values as $key => $value) {
            $saved = $this->set($key, $value, $ttl) && $saved;
        }

        return $saved;
    }

    public function deleteMultiple($keys): bool
    {
        $deleted = true;

        foreach ($keys as $key) {
            $deleted = $this->delete($key) && $deleted;
        }

        return $deleted;
    }

    public function has($key): bool
    {
        foreach ($this->delegates as $cache) {
            if ($cache->has($key)) {
                return true;
            }
        }

        return false;
    }
}
