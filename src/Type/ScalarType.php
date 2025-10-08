<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type;

use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;

/** @internal */
interface ScalarType extends Type
{
    /**
     * Should return true if the value can be casted to this type.
     *
     * `42` can be cast to `string`
     *      —> true
     *
     * `foo` can be cast to `int`
     *      —> false
     */
    public function canCast(mixed $value): bool;

    /**
     * Returns the given value casted to this type. Note that the method
     * `canCast()` must have been called before.
     */
    public function cast(mixed $value): bool|string|int|float;

    public function errorMessage(): ErrorMessage;
}
