<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types\Exception;

use CuyZ\Valinor\Type\ScalarType;
use RuntimeException;

use function get_debug_type;

final class CannotCastValue extends RuntimeException implements CastError
{
    /**
     * @param mixed $value
     */
    public function __construct($value, ScalarType $type)
    {
        $baseType = get_debug_type($value);

        parent::__construct(
            "Cannot cast from `$baseType` to `$type`.",
            1603216198
        );
    }
}
