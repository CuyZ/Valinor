<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fake\Cache;

use Psr\SimpleCache\CacheInterface;

use function count;

/**
 * @implements CacheInterface<mixed>
 */
final class FakeCache implements CacheInterface
{
    /** @var mixed[] */
    private array $entries = [];

    private int $timesSetWasCalled = 0;

    /** @var array<string, int> */
    private array $timesEntryWasSet = [];

    /** @var array<string, int> */
    private array $timesEntryWasFetched = [];

    public function timesEntryWasSet(string $key): int
    {
        return $this->timesEntryWasSet[$key] ?? 0;
    }

    public function timesEntryWasFetched(string $key): int
    {
        return $this->timesEntryWasFetched[$key] ?? 0;
    }

    public function timeSetWasCalled(): int
    {
        return $this->timesSetWasCalled;
    }

    public function countEntries(): int
    {
        return count($this->entries);
    }

    public function get($key, $default = null)
    {
        $this->timesEntryWasFetched[$key] ??= 0;
        $this->timesEntryWasFetched[$key]++;

        return $this->entries[$key] ?? $default;
    }

    public function set($key, $value, $ttl = null): bool
    {
        $this->entries[$key] = $value;

        $this->timesSetWasCalled++;
        $this->timesEntryWasSet[$key] ??= 0;
        $this->timesEntryWasSet[$key]++;

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
