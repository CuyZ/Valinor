<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer;

/** @internal */
interface Transformer
{
    /**
     * @return array<mixed>|scalar|null
     */
    public function transform(mixed $value): mixed;
}
