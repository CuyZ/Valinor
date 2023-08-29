<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer;

/** @api */
interface Normalizer
{
    /**
     * @todo doc
     */
    public function normalize(mixed $value): mixed;
}
