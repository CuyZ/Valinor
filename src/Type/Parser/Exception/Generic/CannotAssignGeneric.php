<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception\Generic;

use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use CuyZ\Valinor\Type\Type;
use RuntimeException;

use function array_map;
use function array_slice;
use function count;
use function implode;

/** @internal */
final class CannotAssignGeneric extends RuntimeException implements InvalidType
{
    /**
     * @param class-string $className
     * @param array<int, string> $templates
     * @param array<int, Type> $generics
     */
    public function __construct(string $className, array $templates, array $generics)
    {
        $generics = array_slice($generics, count($templates));
        $list = implode('`, `', array_map(fn (Type $type) => $type->toString(), $generics));

        parent::__construct("Could not find a template to assign the generic(s) `$list` for the class `$className`.");
    }
}
