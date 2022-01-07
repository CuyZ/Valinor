<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Resolver\Exception;

use CuyZ\Valinor\Type\Type;
use RuntimeException;

/** @internal */
final class InvalidTypeResolvedForInterface extends RuntimeException
{
    public function __construct(string $interfaceName, Type $wrongType)
    {
        parent::__construct(
            "Invalid type `$wrongType`; it must be the name of a class that implements `$interfaceName`.",
            1618049224
        );
    }
}
