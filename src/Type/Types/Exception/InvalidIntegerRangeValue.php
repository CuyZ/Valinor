<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types\Exception;

use CuyZ\Valinor\Type\Types\IntegerRangeType;
use RuntimeException;

/** @api */
final class InvalidIntegerRangeValue extends RuntimeException implements CastError
{
    public function __construct(int $value, IntegerRangeType $type)
    {
        parent::__construct(
            "Invalid value `$value`: it must be an integer between {$type->min()} and {$type->max()}.",
            1638785150
        );
    }
}
