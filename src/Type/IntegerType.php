<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type;

/** @internal */
interface IntegerType extends ScalarType
{
    public function cast(mixed $value): int;
}
