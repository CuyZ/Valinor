<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use RuntimeException;

/** @internal */
final class CannotResolveObjectType extends RuntimeException
{
    public function __construct(string $name)
    {
        parent::__construct("Impossible to resolve an implementation for `$name`.");
    }
}
