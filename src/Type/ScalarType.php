<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type;

use CuyZ\Valinor\Type\Types\Exception\CastError;

interface ScalarType extends Type
{
    /**
     * @param mixed $value
     */
    public function canCast($value): bool;

    /**
     * @param mixed $value
     * @return scalar
     *
     * @throws CastError
     */
    public function cast($value);
}
