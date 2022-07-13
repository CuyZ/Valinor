<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception\Iterable;

use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use CuyZ\Valinor\Type\Type;
use RuntimeException;

/** @internal */
final class IterableClosingBracketMissing extends RuntimeException implements InvalidType
{
    public function __construct(Type $keyType, Type $subtype)
    {
        parent::__construct(
            "The closing bracket is missing for `iterable<{$keyType->toString()}, {$subtype->toString()}>`.",
            1618994728
        );
    }
}
