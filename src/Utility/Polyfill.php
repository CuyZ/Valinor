<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Utility;

/** @internal */
final class Polyfill
{
    /**
     * PHP8.4 use native function `array_all` instead.
     *
     * @param array<mixed> $array
     */
    public static function array_all(array $array, callable $callback): bool
    {
        foreach ($array as $key => $value) {
            if (! $callback($value, $key)) {
                return false;
            }
        }

        return true;
    }
}
