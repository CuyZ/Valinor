<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Exception;

use LogicException;

/** @internal */
final class PropertyNotFound extends LogicException
{
    public function __construct(string $property)
    {
        parent::__construct(
            "The property `$property` does not exist.",
            1_510_936_145
        );
    }
}
