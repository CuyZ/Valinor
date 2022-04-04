<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object\Exception;

use CuyZ\Valinor\Mapper\Tree\Message\Message;
use CuyZ\Valinor\Utility\ValueDumper;
use RuntimeException;

/** @api */
final class InvalidSourceForInterface extends RuntimeException implements Message
{
    /**
     * @param mixed $source
     */
    public function __construct($source)
    {
        $type = ValueDumper::dump($source);

        parent::__construct(
            "Invalid value $type, it must be an iterable.",
            1645283485
        );
    }
}
