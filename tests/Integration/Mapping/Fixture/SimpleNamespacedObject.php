<?php

declare(strict_types=1);

namespace SimpleNamespace;

final class SimpleNamespacedObject
{
    public function __construct(
        /** @var list<string> */
        public array $value,
    ) {}
}
