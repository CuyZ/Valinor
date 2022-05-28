<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type;

use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use UnitEnum;

/** @internal */
interface ScalarType extends Type
{
    /**
     * @param mixed $value
     */
    public function canCast($value): bool;

    /**
     * @param mixed $value
     * @return scalar|UnitEnum
     */
    public function cast($value);

    public function errorMessage(): ErrorMessage;
}
