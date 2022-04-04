<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use CuyZ\Valinor\Mapper\Tree\Message\Message;
use CuyZ\Valinor\Type\Types\ArrayKeyType;
use CuyZ\Valinor\Utility\ValueDumper;
use RuntimeException;

/** @api */
final class InvalidTraversableKey extends RuntimeException implements Message
{
    /**
     * @param string|int $key
     */
    public function __construct($key, ArrayKeyType $type)
    {
        $key = ValueDumper::dump($key);

        parent::__construct(
            "Invalid key $key, it must be of type `$type`.",
            1630946163
        );
    }
}
