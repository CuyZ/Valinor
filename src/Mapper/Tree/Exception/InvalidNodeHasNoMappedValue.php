<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use RuntimeException;

/** @internal */
final class InvalidNodeHasNoMappedValue extends RuntimeException
{
    public function __construct(string $path)
    {
        parent::__construct(
            "Cannot get mapped value for invalid node at path `$path`; use method `\$node->isValid()`.",
            1657466305
        );
    }
}
