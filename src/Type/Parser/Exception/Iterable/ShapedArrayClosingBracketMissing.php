<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception\Iterable;

use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\ShapedArrayElement;
use RuntimeException;

use function array_map;
use function implode;

/** @internal */
final class ShapedArrayClosingBracketMissing extends RuntimeException implements InvalidType
{
    /**
     * @param ShapedArrayElement[] $elements
     */
    public function __construct(array $elements, Type|null|false $unsealedType = null)
    {
        $signature = 'array{' . implode(', ', array_map(fn (ShapedArrayElement $element) => $element->toString(), $elements));

        if ($unsealedType === false) {
            $signature .= ', ...';
        } elseif ($unsealedType instanceof Type) {
            $signature .= ', ...' . $unsealedType->toString();
        }

        parent::__construct("Missing closing curly bracket in shaped array signature `$signature`.");
    }
}
