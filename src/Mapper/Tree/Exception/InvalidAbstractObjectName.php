<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use RuntimeException;

/** @internal */
final class InvalidAbstractObjectName extends RuntimeException
{
    public function __construct(string $name)
    {
        parent::__construct(
            "Invalid interface or class name `$name`.",
            1653990369
        );
    }
}
