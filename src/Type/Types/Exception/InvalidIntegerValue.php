<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types\Exception;

use CuyZ\Valinor\Type\IntegerType;
use RuntimeException;

/** @api */
final class InvalidIntegerValue extends RuntimeException implements CastError
{
    public function __construct(int $value, IntegerType $type)
    {
        parent::__construct(
            "Value `$value` does not match integer value `$type`.",
            1631090798
        );
    }
}
