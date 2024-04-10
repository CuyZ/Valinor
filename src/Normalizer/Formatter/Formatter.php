<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Formatter;

interface Formatter
{
    public function format(mixed $value): mixed;
}
