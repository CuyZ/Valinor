<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type;

/**
 * @PHP8.0 extend Stringable
 *
 * @internal
 */
interface Type
{
    /**
     * @param mixed $value
     */
    public function accepts($value): bool;

    public function matches(self $other): bool;

    public function __toString(): string;
}
