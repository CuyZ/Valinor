<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object\Exception;

use CuyZ\Valinor\Mapper\Object\Factory\SuitableObjectBuilderNotFound;
use CuyZ\Valinor\Mapper\Tree\Message\Message;
use CuyZ\Valinor\Utility\ValueDumper;
use RuntimeException;

/** @api */
final class SeveralObjectBuildersFound extends RuntimeException implements Message, SuitableObjectBuilderNotFound
{
    /**
     * @param mixed $source
     */
    public function __construct($source)
    {
        $value = ValueDumper::dump($source);

        parent::__construct(
            "Value $value is not accepted.",
            1642787246
        );
    }
}
