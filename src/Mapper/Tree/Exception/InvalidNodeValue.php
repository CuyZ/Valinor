<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use CuyZ\Valinor\Mapper\Tree\Message\Message;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Utility\TypeHelper;
use CuyZ\Valinor\Utility\ValueDumper;
use RuntimeException;

/** @api */
final class InvalidNodeValue extends RuntimeException implements Message
{
    /**
     * @param mixed $value
     */
    public function __construct($value, Type $type)
    {
        $value = ValueDumper::dump($value);
        $message = TypeHelper::containsObject($type)
            ? "Value $value is not accepted."
            : "Value $value does not match expected `$type`.";

        parent::__construct($message, 1630678334);
    }
}
