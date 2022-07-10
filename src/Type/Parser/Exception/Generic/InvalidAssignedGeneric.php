<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception\Generic;

use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use CuyZ\Valinor\Type\Type;
use RuntimeException;

/** @internal */
final class InvalidAssignedGeneric extends RuntimeException implements InvalidType
{
    /**
     * @param class-string $className
     */
    public function __construct(Type $generic, Type $template, string $name, string $className)
    {
        parent::__construct(
            "The generic `{$generic->toString()}` is not a subtype of `{$template->toString()}` for " .
            "the template `$name` of the class `$className`.",
            1604613633
        );
    }
}
