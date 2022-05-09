<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type;

/** @api */
interface BooleanType extends ScalarType
{
    public function cast($value): bool;
}
