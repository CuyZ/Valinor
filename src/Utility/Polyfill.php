<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Utility;

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
     * PHP8.0 use native function
     */
    public static function str_contains(string $haystack, string $needle): bool
    {
        return '' === $needle || false !== strpos($haystack, $needle);
    }

    /**
     * PHP8.0 use native function
     */
    public static function str_starts_with(string $haystack, string $needle): bool
    {
        return 0 === strncmp($haystack, $needle, strlen($needle));
    }

    /**
     * PHP8.0 use native function
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
}
