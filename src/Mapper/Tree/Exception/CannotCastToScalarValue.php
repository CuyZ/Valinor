<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use CuyZ\Valinor\Mapper\Tree\Message\Message;
use CuyZ\Valinor\Type\ScalarType;
use CuyZ\Valinor\Utility\ValueDumper;
use RuntimeException;

/** @api */
final class CannotCastToScalarValue extends RuntimeException implements Message
{
    /**
     * @param mixed $value
     */
    public function __construct($value, ScalarType $type)
    {
        if ($value === null || $value === []) {
            $message = "Cannot be empty and must be filled with a value of type `$type`.";
        } else {
            $value = ValueDumper::dump($value);
            $message = "Cannot cast $value to `$type`.";
        }

        parent::__construct($message, 1618736242);
    }
}
