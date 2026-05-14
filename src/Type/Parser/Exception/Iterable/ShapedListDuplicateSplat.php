<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception\Iterable;

use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use CuyZ\Valinor\Type\Types\ShapedArrayElement;
use RuntimeException;

use function array_filter;
use function array_map;
use function implode;

/** @internal */
final class ShapedListDuplicateSplat extends RuntimeException implements InvalidType
{
    /**
     * @param ShapedArrayElement[] $elements
     */
    public function __construct(array $elements)
    {
        $hasOptional = array_filter($elements, fn (ShapedArrayElement $element) => $element->isOptional()) !== [];
        $parts = array_map(
            static fn (ShapedArrayElement $element) => $hasOptional
                ? $element->key()->value() . ($element->isOptional() ? '?: ' : ': ') . $element->type()->toString()
                : $element->type()->toString(),
            $elements,
        );

        $signature = 'list{' . implode(', ', $parts) . ($parts !== [] ? ', ' : '') . '..., ...}';

        parent::__construct(
            "A shaped list can only have one splat element in `$signature`.",
        );
    }
}
