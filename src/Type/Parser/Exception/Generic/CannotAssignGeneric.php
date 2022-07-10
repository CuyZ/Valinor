<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception\Generic;

use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use CuyZ\Valinor\Type\Type;
use RuntimeException;

use function array_map;
use function implode;

/** @internal */
final class CannotAssignGeneric extends RuntimeException implements InvalidType
{
    public function __construct(string $className, Type ...$generics)
    {
        $list = implode('`, `', array_map(fn (Type $type) => $type->toString(), $generics));

        parent::__construct(
            "Could not find a template to assign the generic(s) `$list` for the class `$className`.",
            1604660485
        );
    }
}
