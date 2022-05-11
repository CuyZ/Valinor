<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception\Generic;

use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use CuyZ\Valinor\Type\Type;
use RuntimeException;

use function implode;

/** @internal */
final class CannotAssignGeneric extends RuntimeException implements InvalidType
{
    public function __construct(string $className, Type ...$generics)
    {
        $list = implode('`, `', $generics);

        parent::__construct(
            "Could not find a template to assign the generic(s) `$list` for the class `$className`.",
            1_604_660_485
        );
    }
}
