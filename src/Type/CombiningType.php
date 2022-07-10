<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type;

/** @internal */
interface CombiningType extends CompositeType
{
    public function isMatchedBy(Type $other): bool;

    /**
     * @return Type[]
     */
    public function types(): iterable;
}
