<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Cache;

use CuyZ\Valinor\Normalizer\Transformer\EvaluatedTransformer;
use Psr\SimpleCache\CacheInterface;

/**
 * Simple PSR-16-compatible runtime cache implementation.
 *
 * Used by default by the library so that entries can be cached in memory during
 * runtime.
 *
 * @link http://www.php-fig.org/psr/psr-16/
 *
 * @internal
 *
 * @template EntryType
 * @implements CacheInterface<EntryType>
 */
final class RuntimeCache implements CacheInterface
{
    /** @var array<string, EntryType> */
    private array $entries = [];

    public function get($key, $default = null): mixed
    {
        return $this->entries[$key] ?? $default;
    }

    public function set($key, $value, $ttl = null): bool
    {
        if ($value instanceof EvaluatedTransformer) {
            return false;
        }

        $this->entries[$key] = $value;

        return true;
    }

    public function delete($key): bool
    {
        unset($this->entries[$key]);

        return true;
    }

    public function clear(): bool
    {
        $this->entries = [];

        return true;
    }

    public function getMultiple($keys, $default = null): iterable
    {
        $entries = [];

        foreach ($keys as $key) {
            $entries[$key] = $this->get($key, $default);
        }

        return $entries;
    }

    public function setMultiple($values, $ttl = null): bool
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value, $ttl);
        }

        return true;
    }

    public function deleteMultiple($keys): bool
    {
        foreach ($keys as $key) {
            $this->delete($key);
        }

        return true;
    }

    public function has($key): bool
    {
        return isset($this->entries[$key]);
    }
}
