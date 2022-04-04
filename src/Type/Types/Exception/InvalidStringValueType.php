<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types\Exception;

use CuyZ\Valinor\Utility\ValueDumper;
use RuntimeException;

/** @api */
final class InvalidStringValueType extends RuntimeException implements CastError
{
    /**
     * @param mixed $value
     */
    public function __construct($value, string $stringValue)
    {
        $value = ValueDumper::dump($value);
        $stringValue = ValueDumper::dump($stringValue);

        parent::__construct(
            "Value $value does not match string value $stringValue.",
            1631263954
        );
    }
}
