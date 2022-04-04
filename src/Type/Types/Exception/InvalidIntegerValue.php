<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types\Exception;

use RuntimeException;

/** @api */
final class InvalidIntegerValue extends RuntimeException implements CastError
{
    public function __construct(int $value, int $expected)
    {
        parent::__construct(
            "Value $value does not match expected $expected.",
            1631090798
        );
    }
}
