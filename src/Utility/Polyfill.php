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

    /**
     * PHP8.4 use native function `array_find` instead.
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

    /**
     * PHP8.4 use native function `array_find` instead.
     *
     * @infection-ignore-all
     * @param array<mixed> $array
     */
    public static function array_find(array $array, callable $callback): mixed
    {
        foreach ($array as $key => $value) {
            if ($callback($value, $key)) {
                return $value;
            }
        }

        return null;
    }
}
