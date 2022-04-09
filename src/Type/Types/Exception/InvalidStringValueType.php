<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types\Exception;

use CuyZ\Valinor\Utility\Polyfill;
use RuntimeException;

/** @api */
final class InvalidStringValueType extends RuntimeException implements CastError
{
    /**
     * @param mixed $value
     */
    public function __construct($value, string $stringValue)
    {
        $baseType = Polyfill::get_debug_type($value);

        parent::__construct(
            "Value of type `$baseType` does not match string value `$stringValue`.",
            1631263954
        );
    }
}
