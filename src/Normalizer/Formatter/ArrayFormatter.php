<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Formatter;

use function array_map;
use function is_array;
use function is_iterable;
use function iterator_to_array;

final class ArrayFormatter implements Formatter
{
    public function format(mixed $value): mixed
    {
        if (is_iterable($value)) {
            if (! is_array($value)) {
                $value = iterator_to_array($value);
            }

            $value = array_map($this->format(...), $value);
        }

        return $value;
    }
}
