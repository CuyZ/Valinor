<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer;

use CuyZ\Valinor\Normalizer\Formatter\Formatter;
use CuyZ\Valinor\Normalizer\Formatter\FormatterFactory;

/**
 * @internal
 *
 * @template T
 * @implements Normalizer<T>
 */
final class FormatNormalizer implements Normalizer
{
    /**
     * @param FormatterFactory<Formatter<T>> $formatterFactory
     */
    public function __construct(
        private FormatterFactory $formatterFactory,
        private RecursiveNormalizer $recursiveNormalizer,
    ) {}

    public function normalize(mixed $value): mixed
    {
        return $this->recursiveNormalizer->normalize($value, $this->formatterFactory->new());
    }
}
