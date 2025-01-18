<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Utility;

/** @internal */
final class Polyfill
{
    /**
     * PHP8.4 use native function `array_any` instead.
     *
     * @param array<mixed> $array
     */
    public static function array_any(array $array, callable $callback): bool
    {
        foreach ($array as $key => $value) {
            if ($callback($value, $key)) {
                return true;
            }
        }

        return false;
    }
}
