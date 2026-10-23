<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception\Iterable;

use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\ShapedArrayElement;
use CuyZ\Valinor\Utility\Polyfill;
use RuntimeException;

use function array_map;
use function implode;

/** @internal */
final class InvalidShapedListUnsealedType extends RuntimeException implements InvalidType
{
    public function __construct(Type $unsealedType, ShapedArrayElement ...$elements)
    {
        $hasOptional = Polyfill::array_any(
            $elements,
            static fn (ShapedArrayElement $element) => $element->isOptional(),
        );

        $parts = array_map(
            static fn (ShapedArrayElement $element) => $hasOptional
                ? $element->key()->value() . ($element->isOptional() ? '?: ' : ': ') . $element->type()->toString()
                : $element->type()->toString(),
            $elements,
        );

        $signature = 'list{' . implode(', ', $parts) . ", ...{$unsealedType->toString()}}";

        parent::__construct(
            "Invalid unsealed type in shaped list `$signature`, it should be a valid list but `{$unsealedType->toString()}` was given.",
        );
    }
}
