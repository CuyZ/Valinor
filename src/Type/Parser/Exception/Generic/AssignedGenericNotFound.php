<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception\Generic;

use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use RuntimeException;

use function implode;

/** @internal */
final class AssignedGenericNotFound extends RuntimeException implements InvalidType
{
    public function __construct(string $className, string ...$templates)
    {
        $list = implode('`, `', $templates);

        parent::__construct(
            "No generic was assigned to the template(s) `$list` for the class `$className`.",
            1604656730
        );
    }
}
