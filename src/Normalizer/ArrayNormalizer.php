<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer;

use CuyZ\Valinor\Normalizer\Transformer\EmptyObject;
use CuyZ\Valinor\Normalizer\Transformer\Transformer;

use function array_map;
use function is_array;
use function is_iterable;
use function iterator_to_array;

/**
 * @api
 *
 * @implements Normalizer<array<mixed>|scalar|null>
 */
final class ArrayNormalizer implements Normalizer
{
    /**
     * @internal
     */
    public function __construct(
        private Transformer $transformer,
    ) {}

    /** @pure */
    public function normalize(mixed $value): mixed
    {
        /** @var array<mixed>|scalar|null */
        return $this->format(
            $this->transformer->transform($value),
        );
    }

    private function format(mixed $value): mixed
    {
        if (is_iterable($value)) {
            if (! is_array($value)) {
                $value = iterator_to_array($value);
            }

            $value = array_map($this->format(...), $value);
        } elseif ($value instanceof EmptyObject) {
            return [];
        }

        return $value;
    }
}
