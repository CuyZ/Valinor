<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use CuyZ\Valinor\Mapper\Tree\Message\Message;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Utility\Polyfill;
use RuntimeException;

/** @api */
final class SourceMustBeIterable extends RuntimeException implements Message
{
    /**
     * @param mixed $value
     */
    public function __construct($value, Type $type)
    {
        $valueType = Polyfill::get_debug_type($value);

        $message = "Source must be iterable in order to be cast to `$type`, but is of type `$valueType`.";

        if ($value === null) {
            $message = "Cannot cast an empty value to `$type`.";
        }

        parent::__construct($message, 1618739163);
    }
}
