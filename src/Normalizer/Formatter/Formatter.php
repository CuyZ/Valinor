<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Formatter;

/**
 * @internal
 *
 * @template-covariant T
 */
interface Formatter
{
    /**
     * @param iterable<mixed>|scalar|null $value
     */
    public function push(mixed $value): void;

    /**
     * @return T
     */
    public function value(): mixed;
}
