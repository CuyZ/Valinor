<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fake\Cache;

use CuyZ\Valinor\Cache\Cache;

use CuyZ\Valinor\Cache\CacheEntry;

use function count;

final class FakeCache implements Cache
{
    /** @var array<string, CacheEntry> */
    private array $entries = [];

    private int $timesSetWasCalled = 0;

    /** @var array<string, int> */
    private array $timesEntryWasFetched = [];

    public function timesEntryWasFetched(string $key): int
    {
        return $this->timesEntryWasFetched[$key] ?? 0;
    }

    /**
     * @return array<string, CacheEntry>
     */
    public function entriesThatWereSet(): array
    {
        return $this->entries;
    }

    public function timeSetWasCalled(): int
    {
        return $this->timesSetWasCalled;
    }

    public function countEntries(): int
    {
        return count($this->entries);
    }

    public function get(string $key, mixed ...$arguments): mixed
    {
        $this->timesEntryWasFetched[$key] ??= 0;
        $this->timesEntryWasFetched[$key]++;

        if (! isset($this->entries[$key])) {
            return null;
        }

        /** @var callable $callback */
        $callback = eval('return ' . $this->entries[$key]->code . ';');

        return $callback(...$arguments);
    }

    public function set(string $key, CacheEntry $entry): void
    {
        $this->entries[$key] = $entry;

        $this->timesSetWasCalled++;
    }

    public function clear(): void
    {
        $this->entries = [];
        $this->timesSetWasCalled = 0;
        $this->timesEntryWasFetched = [];
    }
}
