<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception\Iterable;

use CuyZ\Valinor\Type\FixedType;
use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use CuyZ\Valinor\Type\Types\ShapedArrayElement;
use RuntimeException;

use function implode;

/** @internal */
final class ShapedArrayElementTypeMissing extends RuntimeException implements InvalidType
{
    /**
     * @param ShapedArrayElement[] $elements
     */
    public function __construct(array $elements, FixedType $key, bool $optional)
    {
        $signature = 'array{' . implode(', ', $elements);

        if (! empty($elements)) {
            $signature .= ', ';
        }

        $signature .= $key->value();

        if ($optional) {
            $signature .= '?';
        }

        $signature .= ':';

        parent::__construct(
            "Missing element type in shaped array signature `$signature`.",
            1_631_286_250
        );
    }
}
