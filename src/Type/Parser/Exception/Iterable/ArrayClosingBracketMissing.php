<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception\Iterable;

use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use CuyZ\Valinor\Type\Types\ArrayType;
use CuyZ\Valinor\Type\Types\NonEmptyArrayType;
use RuntimeException;

/** @internal */
final class ArrayClosingBracketMissing extends RuntimeException implements InvalidType
{
    public function __construct(ArrayType|NonEmptyArrayType $arrayType)
    {
        parent::__construct(
            "The closing bracket is missing for `{$arrayType->toString()}`.",
            1606483975
        );
    }
}
