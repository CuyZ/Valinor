<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Utility;

use BackedEnum;
use DateTimeInterface;
use UnitEnum;

use function implode;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_object;
use function is_string;
use function str_contains;
use function str_replace;
use function strlen;

/** @internal */
final class ValueDumper
{
    private const MAX_STRING_LENGTH = 50;
    private const MAX_ARRAY_ENTRIES = 5;
    private const DATE_FORMAT = 'Y/m/d H:i:s';

    public static function dump(mixed $value): string
    {
        return self::doDump($value);
    }

    private static function doDump(mixed $value, bool $goDeeper = true): string
    {
        if ($value === null) {
            return 'null';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_int($value) || is_float($value)) {
            return (string)$value;
        }

        if (is_string($value)) {
            $value = self::crop($value);

            if (str_contains($value, "'") && str_contains($value, '"')) {
                return "'" . str_replace("'", "\'", $value) . "'";
            }

            if (str_contains($value, "'")) {
                return '"' . $value . '"';
            }

            return "'" . $value . "'";
        }

        if ($value instanceof BackedEnum) {
            return is_string($value->value)
                ? "'$value->value'"
                : (string)$value->value;
        }

        if ($value instanceof UnitEnum) {
            return "'$value->name'";
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format(self::DATE_FORMAT);
        }

        if (is_object($value)) {
            return 'object(' . $value::class . ')';
        }

        if (is_array($value)) {
            if (empty($value)) {
                return 'array (empty)';
            }

            if (! $goDeeper) {
                return 'array{…}';
            }

            $index = 0;
            $values = [];

            foreach ($value as $key => $val) {
                $values[] = "$key: " . self::doDump($val, false);

                if ($index++ >= self::MAX_ARRAY_ENTRIES) {
                    $values[] = '…';
                    break;
                }
            }

            return 'array{' . implode(', ', $values) . '}';
        }

        // @codeCoverageIgnoreStart
        return 'unknown';
        // @codeCoverageIgnoreEnd
    }

    private static function crop(string $string): string
    {
        if (strlen($string) <= self::MAX_STRING_LENGTH) {
            return $string;
        }

        $string = substr($string, 0, self::MAX_STRING_LENGTH + 1);

        for ($i = strlen($string) - 1; $i > 10; $i--) {
            if ($string[$i] === ' ') {
                return substr($string, 0, $i) . '…';
            }
        }

        return $string . '…';
    }
}
