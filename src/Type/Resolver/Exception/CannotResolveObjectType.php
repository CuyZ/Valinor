<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Resolver\Exception;

use RuntimeException;

/** @internal */
final class CannotResolveObjectType extends RuntimeException
{
    public function __construct(string $name)
    {
        parent::__construct(
            "Impossible to resolve an implementation for `$name`.",
            1618049116
        );
    }
}
