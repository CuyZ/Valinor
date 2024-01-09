<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer;

use CuyZ\Valinor\Normalizer\Formatter\StreamFormatter;
use CuyZ\Valinor\Normalizer\Transformer\RecursiveTransformer;

/**
 * @api
 *
 * @implements Normalizer<resource>
 */
final class StreamNormalizer implements Normalizer
{
    public function __construct(
        private RecursiveTransformer $transformer,
        private StreamFormatter $formatter,
    ) {}

    public function normalize(mixed $value): mixed
    {
        $value = $this->transformer->transform($value);

        $this->formatter->format($value);

        return $this->formatter->resource();
    }
}
