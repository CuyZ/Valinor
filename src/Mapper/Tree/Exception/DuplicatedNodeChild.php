<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use RuntimeException;

/** @internal */
final class DuplicatedNodeChild extends RuntimeException
{
    public function __construct(string $name)
    {
        parent::__construct(
            "The child `$name` is duplicated in the branch.",
            1_634_045_114
        );
    }
}
