<?php

namespace CuyZ\Valinor\Type\Resolver\Union;

use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\UnionType;

interface UnionNarrower
{
    /**
     * @param mixed $source
     */
    public function narrow(UnionType $unionType, $source): Type;
}
