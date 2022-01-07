<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types\Exception;

use RuntimeException;

/** @api */
final class InvalidEmptyStringValue extends RuntimeException implements CastError
{
    public function __construct()
    {
        parent::__construct(
            "Cannot be empty and must be filled with a valid string value.",
            1632925312
        );
    }
}
