<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use CuyZ\Valinor\Mapper\Tree\Message\Message;
use CuyZ\Valinor\Type\ScalarType;
use RuntimeException;

final class CannotCastToScalarValue extends RuntimeException implements Message
{
    /**
     * @param mixed $value
     */
    public function __construct($value, ScalarType $type)
    {
        $valueType = get_debug_type($value);
        $message = "Cannot cast value of type `$valueType` to `$type`.";

        if ($value === null) {
            $message = "Cannot be empty and must be filled with a value of type `$type`.";
        }

        parent::__construct($message, 1618736242);
    }
}
