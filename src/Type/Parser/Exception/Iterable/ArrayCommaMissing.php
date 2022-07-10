<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception\Iterable;

use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\ArrayType;
use CuyZ\Valinor\Type\Types\NonEmptyArrayType;
use RuntimeException;

/** @internal */
final class ArrayCommaMissing extends RuntimeException implements InvalidType
{
    /**
     * @param class-string<ArrayType|NonEmptyArrayType> $arrayType
     */
    public function __construct(string $arrayType, Type $type)
    {
        $signature = "array<{$type->toString()}, ?>";

        if ($arrayType === NonEmptyArrayType::class) {
            $signature = "non-empty-array<{$type->toString()}, ?>";
        }

        parent::__construct(
            "A comma is missing for `$signature`.",
            1606483614
        );
    }
}
