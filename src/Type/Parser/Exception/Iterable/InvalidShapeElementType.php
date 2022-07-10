<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception\Iterable;

use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use CuyZ\Valinor\Type\Types\ShapedArrayElement;
use RuntimeException;

/** @internal */
final class InvalidShapeElementType extends RuntimeException implements InvalidType
{
    public function __construct(ShapedArrayElement $element)
    {
        parent::__construct(
            "The shaped array element `{$element->key()->toString()}` cannot contain a fixed type `{$element->type()->toString()}`.",
            1631294135
        );
    }
}
