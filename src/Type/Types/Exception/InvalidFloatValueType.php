<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types\Exception;

use CuyZ\Valinor\Utility\ValueDumper;
use RuntimeException;

/** @api */
final class InvalidFloatValueType extends RuntimeException implements CastError
{
    /**
     * @param mixed $value
     */
    public function __construct($value, float $floatValue)
    {
        $value = ValueDumper::dump($value);

        parent::__construct(
            "Value $value does not match float value $floatValue.",
            1652110003
        );
    }
}
