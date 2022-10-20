<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type;

/** @internal */
interface BooleanType extends ScalarType
{
    public function cast(mixed $value): bool;
}
