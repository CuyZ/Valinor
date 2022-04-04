<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object\Exception;

use CuyZ\Valinor\Mapper\Object\Arguments;
use CuyZ\Valinor\Mapper\Tree\Message\Message;
use CuyZ\Valinor\Utility\ValueDumper;
use RuntimeException;

/** @api */
final class InvalidSourceForObject extends RuntimeException implements Message
{
    /**
     * @param mixed $source
     */
    public function __construct($source, Arguments $arguments)
    {
        $value = ValueDumper::dump($source);

        parent::__construct(
            "Value $value does not match `{$arguments->signature()}`.",
            1632903281
        );
    }
}
