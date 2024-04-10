<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer;

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
        return $this->transformer->transform($value, new ArrayFormatter());
    }
}
