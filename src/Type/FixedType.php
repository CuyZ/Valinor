<?php

namespace CuyZ\Valinor\Type;

/** @api */
interface FixedType extends Type
{
    /**
     * @return scalar
     */
    public function value();
}
