<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type;

/** @internal */
interface FloatType extends ScalarType
{
    public function cast(mixed $value): float;
}
