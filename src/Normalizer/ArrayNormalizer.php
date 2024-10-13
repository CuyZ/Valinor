<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer;

use CuyZ\Valinor\Normalizer\Transformer\EmptyObject;
use CuyZ\Valinor\Normalizer\Formatter\ArrayFormatter;
use CuyZ\Valinor\Normalizer\Transformer\Transformer;

/**
 * @api
 *
 * @implements Normalizer<array<mixed>|scalar|null>
 */
final class ArrayNormalizer implements Normalizer
{
    public function __construct(
        private Transformer $transformer,
    ) {}

    public function normalize(mixed $value): mixed
    {
        $value = $this->transformer->transform($value, new ArrayFormatter());

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
        } elseif ($value instanceof EmptyObject) {
            return [];
        }

        return $value;
    }
}
