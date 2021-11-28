<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fake\Cache;

use Psr\SimpleCache\CacheInterface;

use function array_keys;

/**
 * @implements CacheInterface<mixed>
 */
final class FakeCache implements CacheInterface
{
    /** @var mixed[] */
    private array $entries = [];

    /**
     * @param mixed $value
     */
    public function replaceAllBy($value): void
    {
        foreach (array_keys($this->entries) as $key) {
            $this->entries[$key] = $value;
        }
    }

    public function get($key, $default = null)
    {
        return $this->entries[$key] ?? $default;
    }

    public function set($key, $value, $ttl = null): bool
    {
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
        foreach ($keys as $key) {
            yield $key => $this->get($key, $default);
        }
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
