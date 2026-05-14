<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception\Iterable;

use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\ShapedArrayElement;
use RuntimeException;

use function array_filter;
use function array_map;
use function implode;

/** @internal */
final class ShapedListClosingBracketMissing extends RuntimeException implements InvalidType
{
    /**
     * @param ShapedArrayElement[] $elements
     */
    public function __construct(array $elements, Type|null|false $unsealedType = null)
    {
        $hasOptional = array_filter($elements, fn (ShapedArrayElement $element) => $element->isOptional()) !== [];
        $parts = array_map(
            static fn (ShapedArrayElement $element) => $hasOptional
                ? $element->key()->value() . ($element->isOptional() ? '?: ' : ': ') . $element->type()->toString()
                : $element->type()->toString(),
            $elements,
        );

        $signature = 'list{' . implode(', ', $parts);

        if ($unsealedType === false) {
            $signature .= ', ...';
        } elseif ($unsealedType instanceof Type) {
            $signature .= ', ...' . $unsealedType->toString();
        }

        parent::__construct("Missing closing curly bracket in shaped list signature `$signature`.");
    }
}
