<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Cache;

/** @internal */
final class CacheEntry
{
    public function __construct(
        public readonly string $code,
        /** @var list<non-empty-string> */
        public readonly array $filesToWatch = [],
    ) {}
}
