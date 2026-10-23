<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception\Iterable;

use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use CuyZ\Valinor\Type\Type;
use RuntimeException;

/** @internal */
final class ShapedArrayWithoutElementsWithSealedType extends RuntimeException implements InvalidType
{
    public function __construct(string $signature, Type $unsealedType)
    {
        $signature .= '...' . $unsealedType->toString() . '}';

        parent::__construct("Missing elements in `$signature`.");
    }
}
