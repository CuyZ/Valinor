<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types\Exception;

use RuntimeException;

/** @api */
final class InvalidNegativeIntegerValue extends RuntimeException implements CastError
{
    public function __construct(int $value)
    {
        parent::__construct(
            "Invalid value `$value`: it must be a negative integer.",
            1632923705
        );
    }
}
