<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception\Iterable;

use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use RuntimeException;

/** @internal */
final class ShapedListNonMonotonicKey extends RuntimeException implements InvalidType
{
    public function __construct(int $key, int $expectedKey, string $signature)
    {
        parent::__construct(
            "Expected monotonically increasing key `$expectedKey`, but got key `$key` in shaped list signature `$signature`.",
            1631283210
        );
    }
}
