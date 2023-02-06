<?php

namespace CuyZ\Valinor\Type;

/** @internal */
interface GenericType extends CompositeType
{
    /**
     * @return array<string, Type>
     */
    public function generics(): array;
}
