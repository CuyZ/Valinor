<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type;

use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;

/** @internal */
interface ScalarType extends Type
{
    public function canCast(mixed $value): bool;

    public function cast(mixed $value): bool|string|int|float;

    public function errorMessage(): ErrorMessage;
}
