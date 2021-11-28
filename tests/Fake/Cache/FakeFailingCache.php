<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fake\Cache;

use Psr\SimpleCache\CacheInterface;

/**
 * @implements CacheInterface<mixed>
 */
final class FakeFailingCache implements CacheInterface
{
    public function get($key, $default = null)
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
