<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types\Exception;

use CuyZ\Valinor\Type\ScalarType;
use CuyZ\Valinor\Utility\ValueDumper;
use RuntimeException;

/** @api */
final class CannotCastValue extends RuntimeException implements CastError
{
    /**
     * @param mixed $value
     */
    public function __construct($value, ScalarType $type)
    {
        $value = ValueDumper::dump($value);

        parent::__construct(
            "Cannot cast $value to `$type`.",
            1603216198
        );
    }
}
