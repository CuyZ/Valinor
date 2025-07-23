<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type;

/** @internal */
interface ObjectType extends Type
{
    /**
     * @return class-string
     */
    public function className(): string;

    public function nativeType(): ObjectType;

}
