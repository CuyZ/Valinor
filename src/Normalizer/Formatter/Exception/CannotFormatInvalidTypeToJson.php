<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Formatter\Exception;

use RuntimeException;

/** @internal */
final class CannotFormatInvalidTypeToJson extends RuntimeException
{
    public function __construct(mixed $value)
    {
        $type = get_debug_type($value);

        parent::__construct(
            "Value of type `$type` cannot be normalized to JSON.",
            1704749897,
        );
    }
}
