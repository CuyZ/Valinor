<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer;

use CuyZ\Valinor\Normalizer\Formatter\StreamFormatter;
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
        private StreamFormatter $formatter,
    ) {}

    /**
     * @return resource
     */
    public function normalize(mixed $value): mixed
    {
        return $this->transformer->transform($value, $this->formatter);
    }
}
