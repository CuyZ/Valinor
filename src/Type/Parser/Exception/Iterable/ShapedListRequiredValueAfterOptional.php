<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception\Iterable;

use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use RuntimeException;

/** @internal */
final class ShapedListRequiredValueAfterOptional extends RuntimeException implements InvalidType
{
    public function __construct(string $key, string $signature)
    {
        parent::__construct(
            "Required value with key `$key` cannot be used after optional value in shaped list signature `$signature`.",
            1631283211
        );
    }
}
