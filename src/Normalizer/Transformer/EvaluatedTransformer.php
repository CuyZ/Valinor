<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer;

use CuyZ\Valinor\Normalizer\Formatter\Formatter;

/** @internal */
final class EvaluatedTransformer implements Transformer
{
    public function __construct(
        private Transformer $delegate,
        /** @var callable(): string */
        private $codeCallback,
    ) {}

    public function transform(mixed $value, Formatter $formatter): mixed
    {
        return $this->delegate->transform($value, $formatter);
    }

    public function code(): string
    {
        return ($this->codeCallback)();
    }
}
