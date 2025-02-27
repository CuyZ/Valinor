<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer;

use CuyZ\Valinor\Normalizer\Formatter\JsonFormatter;
use CuyZ\Valinor\Normalizer\Transformer\Transformer;

/**
 * @api
 *
 * @implements Normalizer<resource>
 */
final class StreamNormalizer implements Normalizer
{
    public function __construct(
        private Transformer $transformer,
        private JsonFormatter $formatter,
    ) {}

    public function normalize(mixed $value): mixed
    {
        $result = $this->transformer->transform($value);

        return $this->formatter->format($result);
    }
}
