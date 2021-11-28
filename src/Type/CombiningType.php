<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type;

interface CombiningType extends Type
{
    public function isMatchedBy(Type $other): bool;

    /**
     * @return Type[]
     */
    public function types(): iterable;
}
