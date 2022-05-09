<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types\Exception;

use RuntimeException;

/** @api */
final class InvalidFloatValue extends RuntimeException implements CastError
{
    public function __construct(float $value, float $expected)
    {
        parent::__construct(
            "Value `$value` does not match expected value `$expected`.",
            1652110115
        );
    }
}
