<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types\Exception;

use CuyZ\Valinor\Utility\ValueDumper;
use RuntimeException;

/** @api */
final class InvalidIntegerValueType extends RuntimeException implements CastError
{
    /**
     * @param mixed $value
     */
    public function __construct($value, int $integerValue)
    {
        $value = ValueDumper::dump($value);

        parent::__construct(
            "Value $value does not match integer value $integerValue.",
            1631267159
        );
    }
}
