<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type;

/** @internal */
interface StringType extends ScalarType
{
    public function cast(mixed $value): string;
}
