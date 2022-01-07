<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type;

/** @api */
interface StringType extends ScalarType
{
    public function cast($value): string;
}
