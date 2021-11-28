<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception\Iterable;

use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\ArrayType;
use CuyZ\Valinor\Type\Types\NonEmptyArrayType;
use RuntimeException;

final class InvalidArrayKey extends RuntimeException implements InvalidType
{
    /**
     * @param class-string<ArrayType|NonEmptyArrayType> $arrayType
     */
    public function __construct(string $arrayType, Type $keyType, Type $subType)
    {
        $signature = "array<$keyType, $subType>";

        if ($arrayType === NonEmptyArrayType::class) {
            $signature = "non-empty-array<$keyType, $subType>";
        }

        parent::__construct(
            "Invalid key type `$keyType` for `$signature`. It must be one of `array-key`, `int` or `string`.",
            1604335007
        );
    }
}
