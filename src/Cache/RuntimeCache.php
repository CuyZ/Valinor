<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Cache;

/**
 * @internal
 *
 * @template EntryType
 * @implements Cache<EntryType>
 */
final class RuntimeCache implements Cache
{
    /** @var array<string, EntryType|null> */
    private array $entries = [];

    public function __construct(
        /** @var Cache<EntryType> */
        private Cache $delegate,
    ) {}

    public function get(string $key, mixed ...$arguments): mixed
    {
        return $this->entries[$key] ??= $this->delegate->get($key, ...$arguments);
    }

    public function set(string $key, CacheEntry $entry): void
    {
        $this->delegate->set($key, $entry);
    }

    public function clear(): void
    {
        $this->entries = [];

        $this->delegate->clear();
    }
}
