<?php

namespace CuyZ\Valinor\Type;

/** @internal */
interface GenericType extends ObjectType, CompositeType
{
    /**
     * @return class-string
     */
    public function className(): string;

    /**
     * @return array<non-empty-string, Type>
     */
    public function generics(): array;
}
