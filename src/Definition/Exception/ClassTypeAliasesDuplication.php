<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Exception;

use LogicException;

use function implode;

/** @internal */
final class ClassTypeAliasesDuplication extends LogicException
{
    /**
     * @param class-string $className
     */
    public function __construct(string $className, string ...$names)
    {
        $names = implode('`, `', $names);

        parent::__construct(
            "The following type aliases already exist in class `$className`: `$names`.",
            1638477604
        );
    }
}
