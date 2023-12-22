<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Formatter;

use function is_array;
use function is_iterable;
use function iterator_to_array;

/**
 * @internal
 *
 * @implements Formatter<array<mixed>|scalar|null>
 */
final class ArrayFormatter implements Formatter
{
    /** @var iterable<mixed>|scalar|null */
    private mixed $value;

    public function push(mixed $value): void
    {
        $this->value = $value;
    }

    public function value(): mixed
    {
        if (is_iterable($this->value) && ! is_array($this->value)) {
            $this->value = iterator_to_array($this->value);
        }

        return $this->value;
    }
}
