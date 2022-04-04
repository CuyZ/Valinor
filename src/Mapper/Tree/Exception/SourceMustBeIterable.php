<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use CuyZ\Valinor\Mapper\Tree\Message\Message;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Utility\TypeHelper;
use CuyZ\Valinor\Utility\ValueDumper;
use RuntimeException;

/** @api */
final class SourceMustBeIterable extends RuntimeException implements Message
{
    /**
     * @param mixed $value
     */
    public function __construct($value, Type $type)
    {
        if ($value === null) {
            $message = TypeHelper::containsObject($type)
                ? 'Cannot be empty.'
                : "Cannot be empty and must be filled with a value matching `$type`.";
        } else {
            $value = ValueDumper::dump($value);
            $message = TypeHelper::containsObject($type)
                ? "Value $value is not accepted."
                : "Value $value does not match expected `$type`.";
        }

        parent::__construct($message, 1618739163);
    }
}
