<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception\Iterable;

use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use CuyZ\Valinor\Type\Type;
use RuntimeException;

/** @internal */
final class ShapedArrayWithoutElementsWithSealedType extends RuntimeException implements InvalidType
{
    public function __construct(Type $unsealedType)
    {
        $signature = "array{...{$unsealedType->toString()}}";

        parent::__construct("Missing elements in shaped array signature `$signature`.");
    }
}
