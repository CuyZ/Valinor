<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types\Exception;

use CuyZ\Valinor\Utility\Polyfill;
use RuntimeException;

/** @api */
final class InvalidFloatValueType extends RuntimeException implements CastError
{
    /**
     * @param mixed $value
     */
    public function __construct($value, float $floatValue)
    {
        $baseType = Polyfill::get_debug_type($value);

        parent::__construct(
            "Value of type `$baseType` does not match float value `$floatValue`.",
            1652110003
        );
    }
}
