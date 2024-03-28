<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception\Iterable;

use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\ShapedArrayElement;
use RuntimeException;

use function implode;

/** @internal */
final class ShapedArrayInvalidUnsealedType extends RuntimeException implements InvalidType
{
    /**
     * @param ShapedArrayElement[] $elements
     */
    public function __construct(array $elements, Type $unsealedType)
    {
        $signature = 'array{';
        $signature .= implode(', ', array_map(fn (ShapedArrayElement $element) => $element->toString(), $elements));
        $signature .= ', ...' . $unsealedType->toString();
        $signature .= '}';

        parent::__construct(
            "Invalid unsealed type `{$unsealedType->toString()}` in shaped array signature `$signature`, it should be a valid array.",
            1711618899,
        );
    }
}
