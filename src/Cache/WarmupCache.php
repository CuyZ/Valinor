<?php

namespace CuyZ\Valinor\Cache;

use Psr\SimpleCache\CacheInterface;

/**
 * @internal
 *
 * @template T
 * @extends CacheInterface<T>
 */
interface WarmupCache extends CacheInterface
{
    public function warmup(): void;
}
