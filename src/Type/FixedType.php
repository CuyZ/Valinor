<?php

namespace CuyZ\Valinor\Type;

interface FixedType extends Type
{
    /**
     * @return scalar
     */
    public function value();
}
