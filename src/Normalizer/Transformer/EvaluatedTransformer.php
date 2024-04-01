<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer;

/** @internal */
final class EvaluatedTransformer implements Transformer
{
    public function __construct(
        private Transformer $delegate,
        /** @var callable(): string */
        private $codeCallback,
    ) {}

    public function transform(mixed $value): mixed
    {
        return $this->delegate->transform($value);
    }

    public function code(): string
    {
        return ($this->codeCallback)();
    }
}
