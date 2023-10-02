<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fake\Cache;

use CuyZ\Valinor\Cache\WarmupCache;

/**
 * @implements WarmupCache<mixed>
 */
final class FakeCacheWithWarmup implements WarmupCache
{
    private int $warmupCount = 0;

    public function timesWarmupWasCalled(): int
    {
        return $this->warmupCount;
    }

    public function warmup(): void
    {
        $this->warmupCount++;
    }

    public function get($key, $default = null): mixed
    {
        return null;
    }

    public function set($key, $value, $ttl = null): bool
    {
        return false;
    }

    public function delete($key): bool
    {
        return false;
    }

    public function clear(): bool
    {
        return false;
    }

    public function getMultiple($keys, $default = null): iterable
    {
        return [];
    }

    public function setMultiple($values, $ttl = null): bool
    {
        return false;
    }

    public function deleteMultiple($keys): bool
    {
        return false;
    }

    public function has($key): bool
    {
        return false;
    }
}
