<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception\Iterable;

use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use CuyZ\Valinor\Type\Types\ShapedArrayElement;
use RuntimeException;

use function array_map;
use function implode;

/** @internal */
final class ShapedArrayDuplicateSplat extends RuntimeException implements InvalidType
{
    /**
     * @param ShapedArrayElement[] $elements
     */
    public function __construct(array $elements)
    {
        $parts = array_map(
            static fn (ShapedArrayElement $element) => $element->toString(),
            $elements,
        );

        $signature = 'array{' . implode(', ', $parts) . ($parts !== [] ? ', ' : '') . '..., ...}';

        parent::__construct(
            "A shaped array can only have one splat element in `$signature`.",
        );
    }
}
