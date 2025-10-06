<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types\Factory;

use LogicException;

use function gettype;

/** @internal */
final class CannotBuildTypeFromValue extends LogicException
{
    public function __construct(mixed $value)
    {
        $type = gettype($value);

        parent::__construct("Cannot build type from value of type `$type`.");
    }
}
