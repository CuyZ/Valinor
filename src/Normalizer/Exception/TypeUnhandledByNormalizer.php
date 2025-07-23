<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Exception;

use RuntimeException;

use function get_debug_type;

/** @internal */
final class TypeUnhandledByNormalizer extends RuntimeException
{
    public function __construct(mixed $value)
    {
        $type = get_debug_type($value);

        parent::__construct(
            "Value of type `$type` cannot be normalized.",
            1695062925,
        );
    }
}
