<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type;

/** @api */
interface IntegerType extends ScalarType
{
    public function cast($value): int;
}
