<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer;

/** @internal */
final class AlreadyNormalizedValue
{
    public function __construct(
        public mixed $value,
    ) {}
}
