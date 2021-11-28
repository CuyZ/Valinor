<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use CuyZ\Valinor\Mapper\Tree\Message\Message;
use CuyZ\Valinor\Type\Type;
use RuntimeException;

final class InvalidNodeValue extends RuntimeException implements Message
{
    /**
     * @param mixed $value
     */
    public function __construct($value, Type $type)
    {
        $valueType = get_debug_type($value);

        $message = "Value of type `$valueType` is not accepted by type `$type`.";

        if ($value === []) {
            $message = "Empty array is not accepted by `$type`.";
        }

        parent::__construct($message, 1630678334);
    }
}
