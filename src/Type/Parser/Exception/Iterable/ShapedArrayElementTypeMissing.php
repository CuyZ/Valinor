<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception\Iterable;

use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use CuyZ\Valinor\Type\Types\IntegerValueType;
use CuyZ\Valinor\Type\Types\ShapedArrayElement;
use CuyZ\Valinor\Type\Types\StringValueType;
use RuntimeException;

use function array_map;
use function implode;

/** @internal */
final class ShapedArrayElementTypeMissing extends RuntimeException implements InvalidType
{
    /**
     * @param ShapedArrayElement[] $elements
     */
    public function __construct(array $elements, StringValueType|IntegerValueType $key, bool $optional)
    {
        $signature = 'array{' . implode(', ', array_map(fn (ShapedArrayElement $element) => $element->toString(), $elements));

        if (! empty($elements)) {
            $signature .= ', ';
        }

        $signature .= $key->value();

        if ($optional) {
            $signature .= '?';
        }

        $signature .= ':';

        parent::__construct("Missing element type in shaped array signature `$signature`.");
    }
}
