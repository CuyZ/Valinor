<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type;

/** @internal */
interface CompositeType extends Type
{
    /**
     * @return iterable<Type>
     */
    public function traverse(): iterable;
}
