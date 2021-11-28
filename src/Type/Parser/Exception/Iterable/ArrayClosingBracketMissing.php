<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception\Iterable;

use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\ArrayType;
use CuyZ\Valinor\Type\Types\NonEmptyArrayType;
use RuntimeException;

final class ArrayClosingBracketMissing extends RuntimeException implements InvalidType
{
    /**
     * @param ArrayType|NonEmptyArrayType $arrayType
     */
    public function __construct(Type $arrayType)
    {
        parent::__construct(
            "The closing bracket is missing for `$arrayType`.",
            1606483975
        );
    }
}
