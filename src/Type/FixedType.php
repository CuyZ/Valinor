<?php

namespace CuyZ\Valinor\Type;

/** @internal */
interface FixedType extends Type
{
    /**
     * @return scalar
     */
    public function value();
}
