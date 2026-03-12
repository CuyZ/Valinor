<?php

namespace CuyZ\Valinor\Normalizer\Transformer\Common;

use DateTimeInterface;

/** @api */
final class CustomDateTimeTransformer
{
    public function __construct(private string $format) {}

    public function __invoke(DateTimeInterface $date): string
    {
        return $date->format($this->format);
    }
}
