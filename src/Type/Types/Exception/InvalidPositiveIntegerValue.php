<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types\Exception;

use RuntimeException;

/** @api */
final class InvalidPositiveIntegerValue extends RuntimeException implements CastError
{
    public function __construct(int $value)
    {
        parent::__construct(
            "Invalid value `$value`: it must be a positive integer.",
            1632923676
        );
    }
}
