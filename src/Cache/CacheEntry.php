<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Cache;

/** @internal */
final readonly class CacheEntry
{
    public function __construct(
        public string $code,
        /** @var list<non-empty-string> */
        public array $filesToWatch = [],
    ) {}
}
