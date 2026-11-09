<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Utility\String;

use IntlException;
use MessageFormatter;

use function class_exists;
use function intl_get_error_message;
use function json_decode;
use function json_encode;
use function preg_match;
use function preg_quote;
use function preg_replace;

use const JSON_INVALID_UTF8_SUBSTITUTE;
use const JSON_THROW_ON_ERROR;

/** @internal */
final class StringFormatter
{
    public const DEFAULT_LOCALE = 'en';

    /**
     * @param array<string, string> $parameters
     */
    public static function format(string $locale, string $body, array $parameters = []): string
    {
        foreach ($parameters as $name => $value) {
            if (preg_match('//u', $value) === false) {
                // JSON replaces malformed UTF-8 without requiring optional extensions.
                /** @var string $value */
                $value = json_decode(json_encode($value, JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR), flags: JSON_THROW_ON_ERROR);
                $parameters[$name] = $value;
            }
        }

        return class_exists(MessageFormatter::class)
            ? self::formatWithIntl($locale, $body, $parameters)
            : self::formatWithRegex($body, $parameters);
    }

    /**
     * @param array<string, string> $parameters
     */
    private static function formatWithIntl(string $locale, string $body, array $parameters): string
    {
        try {
            $formatted = MessageFormatter::formatMessage($locale, $body, $parameters);

            if ($formatted === false) {
                throw new StringFormatterError($body, intl_get_error_message());
            }

            return $formatted;
        } catch (IntlException $e) {
            throw new StringFormatterError($body, $e->getMessage(), $e);
        }
    }

    /**
     * @param array<string, string> $parameters
     */
    private static function formatWithRegex(string $body, array $parameters): string
    {
        $message = $body;

        if (preg_match('/{\s*[^}]*[^}a-z_]+\s*}?/', $body)) {
            throw new StringFormatterError($body);
        }

        foreach ($parameters as $name => $value) {
            $name = preg_quote($name, '/');

            /** @var string $message */
            $message = preg_replace("/{\s*$name\s*}/", $value, $message);
        }

        return $message;
    }
}
