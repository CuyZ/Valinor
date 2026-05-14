<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception\Iterable;

use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use CuyZ\Valinor\Type\Types\IntegerValueType;
use CuyZ\Valinor\Type\Types\ShapedArrayElement;
use RuntimeException;

use function array_map;
use function implode;

/** @internal */
final class ShapedListElementTypeMissing extends RuntimeException implements InvalidType
{
    /**
     * @param ShapedArrayElement[] $elements
     */
    public function __construct(array $elements, IntegerValueType $key, bool $optional)
    {
        $hasOptional = $optional;

        if (! $hasOptional) {
            $hasOptional = self::hasOptionalElement($elements);
        }

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

        $signature .= $key->value();

        if ($optional) {
            $signature .= '?';
        }

        $signature .= ':';

        parent::__construct("Missing element type in shaped list signature `$signature`.");
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
