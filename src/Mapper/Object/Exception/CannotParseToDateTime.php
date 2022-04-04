<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object\Exception;

use CuyZ\Valinor\Mapper\Tree\Message\Message;
use CuyZ\Valinor\Utility\ValueDumper;
use RuntimeException;

/** @api */
final class CannotParseToDateTime extends RuntimeException implements Message
{
    public function __construct(string $datetime)
    {
        $datetime = ValueDumper::dump($datetime);

        parent::__construct(
            "Impossible to parse date with value $datetime.",
            1630686564
        );
    }
}
