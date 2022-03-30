<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object\Exception;

use CuyZ\Valinor\Mapper\Object\Arguments;
use OutOfBoundsException;

/** @internal */
final class InvalidArgumentIndex extends OutOfBoundsException
{
    public function __construct(int $index, Arguments $arguments)
    {
        $max = $arguments->count() - 1;

        parent::__construct(
            "Index $index is out of range, it should be between 0 and $max.",
            1648672136
        );
    }
}
