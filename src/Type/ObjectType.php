<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type;

/** @api */
interface ObjectType extends Type
{
    /**
     * @return class-string
     */
    public function className(): string;

    /**
     * @return array<string, Type>
     */
    public function generics(): array;
}
