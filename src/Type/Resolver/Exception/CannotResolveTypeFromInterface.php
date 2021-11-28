<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Resolver\Exception;

use RuntimeException;

final class CannotResolveTypeFromInterface extends RuntimeException
{
    public function __construct(string $interfaceName)
    {
        parent::__construct(
            "Impossible to resolve an implementation for the interface `$interfaceName`.",
            1618049116
        );
    }
}
