<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer;

use CuyZ\Valinor\Normalizer\Transformer\RecursiveTransformer;

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
    public function __construct(
        private RecursiveTransformer $transformer,
    ) {}

    public function normalize(mixed $value): mixed
    {
        $value = $this->transformer->transform($value);

        /** @var array<mixed>|scalar|null */
        return $this->normalizeIterator($value);
    }

    private function normalizeIterator(mixed $value): mixed
    {
        if (is_iterable($value)) {
            if (! is_array($value)) {
                $value = iterator_to_array($value);
            }

            $value = array_map($this->normalizeIterator(...), $value);
        }

        return $value;
    }
}
