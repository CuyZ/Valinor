<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types\Factory;

use LogicException;

use function gettype;

/** @internal */
final class CannotBuildTypeFromValue extends LogicException
{
    /**
     * @param mixed $value
     */
    public function __construct($value)
    {
        $type = gettype($value);

        parent::__construct(
            "Cannot build type from value of type `$type`.",
            1653592997
        );
    }
}
