<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types\Exception;

use RuntimeException;

/** @api */
final class InvalidIntegerValueType extends RuntimeException implements CastError
{
    /**
     * @param mixed $value
     */
    public function __construct($value, int $integerValue)
    {
        $baseType = get_debug_type($value);

        parent::__construct(
            "Value of type `$baseType` does not match integer value `$integerValue`.",
            1631267159
        );
    }
}
