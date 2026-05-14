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
final class ShapedListColonTokenMissing extends RuntimeException implements InvalidType
{
    /**
     * @param ShapedArrayElement[] $elements
     */
    public function __construct(array $elements, Type $type)
    {
        $hasOptional = self::hasOptionalElement($elements);
        $parts = array_map(
            static fn (ShapedArrayElement $element) => $hasOptional
                ? $element->key()->value() . ($element->isOptional() ? '?: ' : ': ') . $element->type()->toString()
                : $element->type()->toString(),
            $elements,
        );

        $signature = 'list{' . implode(', ', $parts);

        if (! empty($elements)) {
            $signature .= ', ';
        }

        $signature .= "{$type->toString()}?";

        parent::__construct("A colon symbol is missing in shaped list signature `$signature`.");
    }

    /**
     * @param array<ShapedArrayElement> $elements
     */
    private static function hasOptionalElement(array $elements): bool
    {
        foreach ($elements as $element) {
            if ($element->isOptional()) {
                return true;
            }
        }

        return false;
    }
}
