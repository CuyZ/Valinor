<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Library;

use Psr\SimpleCache\CacheInterface;

use function krsort;

/** @internal */
final class NormalizerSettings
{
    /** @var CacheInterface<mixed>|null */
    public ?CacheInterface $cache = null;

    /** @var array<int, list<callable>> */
    public array $handlers = [];

    /**
     * @return array<callable>
     */
    public function sortedHandlers(): array
    {
        krsort($this->handlers);

        $callables = [];

        foreach ($this->handlers as $list) {
            $callables = [...$callables, ...$list];
        }

        return $callables;
    }
}
