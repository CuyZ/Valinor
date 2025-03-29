<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer;

/** @internal */
final class EvaluatedTransformer
{
    public function __construct(
        public readonly string $code,
    ) {}
}
