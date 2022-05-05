<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type;

use CuyZ\Valinor\Type\Types\ArrayKeyType;

/** @api */
interface CompositeTraversableType extends CompositeType
{
    public function keyType(): ArrayKeyType;

    public function subType(): Type;
}
