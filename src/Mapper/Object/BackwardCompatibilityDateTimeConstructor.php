<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object;

use CuyZ\Valinor\Mapper\Object\Exception\CannotParseToBackwardCompatibilityDateTime;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;

/**
 * @deprecated This class is only here to support the old datetime mapping
 *             behaviour. It can be used to temporarily have the same behaviour
 *             during the mapping. A migration to {@see DateTimeFormatConstructor} is
 *             strongly recommended.
 *
 * Usage:
 *
 * ```php
 * (new \CuyZ\Valinor\MapperBuilder())
 *     ->registerConstructor(new BackwardCompatibilityDateTimeConstructor())
 *     ->mapper()
 *     ->map(SomeClass::class, […]);
 * ```
 *
 * @api
 */
final class BackwardCompatibilityDateTimeConstructor
{
    public const DATE_MYSQL = 'Y-m-d H:i:s';
    public const DATE_PGSQL = 'Y-m-d H:i:s.u';
    public const DATE_WITHOUT_TIME = '!Y-m-d';

    /**
     * @param class-string<DateTime|DateTimeImmutable> $className
     * @param non-empty-string|positive-int|array{datetime: non-empty-string|positive-int, format?: ?non-empty-string} $value
     */
    #[DynamicConstructor]
    public function __invoke(string $className, $value): DateTimeInterface
    {
        $datetime = $value;
        $format = null;

        if (is_array($datetime)) {
            $format = $datetime['format'] ?? null;
            $datetime = $datetime['datetime'];
        }

        if ($format) {
            $date = $this->tryFormat($className, (string)$datetime, $format);
        } elseif (is_int($datetime)) {
            $date = $this->tryFormat($className, (string)$datetime, 'U');
        } else {
            $date = $this->tryAllFormats($className, $datetime);
        }

        if (! $date) {
            // @PHP8.0 use throw exception expression
            throw new CannotParseToBackwardCompatibilityDateTime();
        }

        return $date;
    }

    /**
     * @param class-string<DateTime|DateTimeImmutable> $className
     */
    private function tryAllFormats(string $className, string $value): ?DateTimeInterface
    {
        $formats = [
            self::DATE_MYSQL, self::DATE_PGSQL, DATE_ATOM, DATE_RFC850, DATE_COOKIE,
            DATE_RFC822, DATE_RFC1036, DATE_RFC1123, DATE_RFC2822, DATE_RFC3339,
            DATE_RFC3339_EXTENDED, DATE_RFC7231, DATE_RSS, DATE_W3C, self::DATE_WITHOUT_TIME,
        ];

        foreach ($formats as $format) {
            $date = $this->tryFormat($className, $value, $format);

            if ($date instanceof DateTimeInterface) {
                return $date;
            }
        }

        return null;
    }

    /**
     * @param class-string<DateTime|DateTimeImmutable> $className
     */
    private function tryFormat(string $className, string $value, string $format): ?DateTimeInterface
    {
        return $className::createFromFormat($format, $value) ?: null;
    }
}
