<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer;

use CuyZ\Valinor\Normalizer\Formatter\Formatter;

/** @internal */
interface Transformer
{
    /**
     * @return array<mixed>|scalar|null
     */
    public function transform(mixed $value, Formatter $formatter): mixed;
}
