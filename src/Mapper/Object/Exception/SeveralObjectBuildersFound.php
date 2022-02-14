<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object\Exception;

use CuyZ\Valinor\Mapper\Object\Factory\SuitableObjectBuilderNotFound;
use CuyZ\Valinor\Mapper\Tree\Message\Message;
use RuntimeException;

use function get_debug_type;

/** @api */
final class SeveralObjectBuildersFound extends RuntimeException implements Message, SuitableObjectBuilderNotFound
{
    /**
     * @param mixed $source
     */
    public function __construct($source)
    {
        $type = get_debug_type($source);

        parent::__construct(
            "Could not map input of type `$type`.",
            1642787246
        );
    }
}
