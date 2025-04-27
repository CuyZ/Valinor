<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Cache;

/**
 * @internal
 *
 * @template T = mixed
 */
interface Cache
{
    /**
     * @param non-empty-string $key
     * @return null|T
     */
    public function get(string $key, mixed ...$arguments): mixed;

    /**
     * @param non-empty-string $key
     */
    public function set(string $key, CacheEntry $entry): void;

    public function clear(): void;
}
