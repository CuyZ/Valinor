<?php

namespace CuyZ\Valinor\Type;

/** @internal */
interface FixedType extends Type
{
    public function value(): bool|string|int|float;
}
