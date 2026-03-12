<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Utility;

use function function_exists;

/** @internal */
final class Polyfill
{
    /**
     * PHP8.4 use native function `array_all` instead.
     *
     * @infection-ignore-all
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
     * @infection-ignore-all
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

    /**
     * @return non-empty-string
     */
    public static function array_all_name(): string
    {
        // @infection-ignore-all
        return function_exists('array_all') ? 'array_all' : self::class . '::array_all';
    }
}
