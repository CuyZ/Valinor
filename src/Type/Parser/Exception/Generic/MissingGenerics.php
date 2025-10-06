<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception\Generic;

use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use CuyZ\Valinor\Type\Type;
use RuntimeException;

use function array_fill;
use function array_map;
use function count;
use function implode;

/** @internal */
final class MissingGenerics extends RuntimeException implements InvalidType
{
    /**
     * @param class-string $className
     * @param Type[] $generics
     * @param array<non-empty-string> $templates
     */
    public function __construct(string $className, array $generics, array $templates)
    {
        /** @var positive-int $missing */
        $missing = count($templates) - count($generics);
        $generics = array_map(fn (Type $type) => $type->toString(), $generics);
        $generics += array_fill(count($generics), $missing, '?');

        $signature = $className . '<' . implode(', ', $generics) . '>';

        parent::__construct("There are $missing missing generics for `$signature`.");
    }
}
