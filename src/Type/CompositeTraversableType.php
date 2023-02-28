<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type;

/** @internal */
interface CompositeTraversableType extends CompositeType
{
    public function keyType(): Type;

    public function subType(): Type;
}
