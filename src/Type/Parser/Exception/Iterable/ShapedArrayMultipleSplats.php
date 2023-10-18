<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception\Iterable;

use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use CuyZ\Valinor\Type\Types\ShapedArrayElement;
use RuntimeException;

use function implode;

/** @internal */
final class ShapedArrayMultipleSplats extends RuntimeException implements InvalidType
{
    /**
     * @param ShapedArrayElement[] $elements
     */
    public function __construct(array $elements)
    {
        $signature = 'array{' . implode(', ', array_map(fn (ShapedArrayElement $element) => $element->toString(), $elements));

        parent::__construct(
            "Multiple `...` tokens found in shaped array `$signature`.",
            1631286590
        );
    }
}
