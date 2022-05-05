<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Utility;

use __PHP_Incomplete_Class;

use function class_implements;
use function get_class;
use function get_parent_class;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_object;
use function is_string;
use function key;
use function strlen;
use function strncmp;
use function strpos;
use function substr_compare;

/**
 * @internal
 *
 * @codeCoverageIgnore
 * @infection-ignore-all
 */
final class Polyfill
{
    /**
     * @PHP8.0 use native function
     */
    public static function str_contains(string $haystack, string $needle): bool
    {
        return '' === $needle || false !== strpos($haystack, $needle);
    }

    /**
     * @PHP8.0 use native function
     */
    public static function str_starts_with(string $haystack, string $needle): bool
    {
        return 0 === strncmp($haystack, $needle, strlen($needle));
    }

    /**
     * @PHP8.0 use native function
     */
    public static function str_ends_with(string $haystack, string $needle): bool
    {
        if ('' === $needle || $needle === $haystack) {
            return true;
        }

        if ('' === $haystack) {
            return false;
        }

        $needleLength = strlen($needle);

        return $needleLength <= strlen($haystack) && 0 === substr_compare($haystack, $needle, -$needleLength);
    }

    /**
     * @PHP8.0 use native function
     *
     * @param mixed $value
     */
    public static function get_debug_type($value): string
    {
        switch (true) {
            case null === $value:
                return 'null';
            case is_bool($value):
                return 'bool';
            case is_string($value):
                return 'string';
            case is_array($value):
                return 'array';
            case is_int($value):
                return 'int';
            case is_float($value):
                return 'float';
            case is_object($value):
                break;
            case $value instanceof __PHP_Incomplete_Class: // @phpstan-ignore-line
                return '__PHP_Incomplete_Class';
            default:
                // @phpstan-ignore-next-line
                if (null === $type = @get_resource_type($value)) {
                    return 'unknown';
                }

                if ('Unknown' === $type) {
                    $type = 'closed';
                }

                return "resource ($type)";
        }

        $class = get_class($value);

        if (false === strpos($class, '@')) {
            return $class;
        }

        // @phpstan-ignore-next-line
        return (get_parent_class($class) ?: key(class_implements($class)) ?: 'class') . '@anonymous';
    }
}
