<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type;

/** @internal */
interface CombiningType extends CompositeType
{
    public function isMatchedBy(Type $other): bool;

    /**
     * @return non-empty-list<Type>
     */
    public function types(): array;
}
