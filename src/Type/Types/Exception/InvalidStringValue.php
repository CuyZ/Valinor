<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types\Exception;

use RuntimeException;

/** @api */
final class InvalidStringValue extends RuntimeException implements CastError
{
    public function __construct(string $value, string $other)
    {
        parent::__construct(
            "Values `$value` and `$other` do not match.",
            1631263740
        );
    }
}
