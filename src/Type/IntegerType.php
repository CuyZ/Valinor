<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type;

interface IntegerType extends ScalarType
{
    public function cast($value): int;
}
