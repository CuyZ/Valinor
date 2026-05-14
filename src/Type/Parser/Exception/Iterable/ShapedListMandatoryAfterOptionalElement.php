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
final class ShapedListMandatoryAfterOptionalElement extends RuntimeException implements InvalidType
{
    public function __construct(int $index, ShapedArrayElement ...$elements)
    {
        $hasOptional = array_filter($elements, fn (ShapedArrayElement $element) => $element->isOptional()) !== [];
        $parts = array_map(
            static fn (ShapedArrayElement $element) => $hasOptional
                ? $element->key()->value() . ($element->isOptional() ? '?: ' : ': ') . $element->type()->toString()
                : $element->type()->toString(),
            $elements,
        );

        $signature = 'list{' . implode(', ', $parts) . '}';

        parent::__construct(
            "Mandatory element at position $index cannot follow an optional element in shaped list `$signature`.",
        );
    }
}
