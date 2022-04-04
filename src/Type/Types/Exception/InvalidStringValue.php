<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types\Exception;

use CuyZ\Valinor\Utility\ValueDumper;
use RuntimeException;

/** @api */
final class InvalidStringValue extends RuntimeException implements CastError
{
    public function __construct(string $value, string $expected)
    {
        $value = ValueDumper::dump($value);
        $expected = ValueDumper::dump($expected);

        parent::__construct(
            "Value $value does not match expected $expected.",
            1631263740
        );
    }
}
