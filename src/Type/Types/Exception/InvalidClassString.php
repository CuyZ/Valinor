<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types\Exception;

use CuyZ\Valinor\Type\Type;
use LogicException;

final class InvalidClassString extends LogicException implements CastError
{
    public function __construct(string $raw, Type $type)
    {
        parent::__construct(
            "Invalid class string `$raw`, it must be a subtype of `$type`.",
            1608132562
        );
    }
}
