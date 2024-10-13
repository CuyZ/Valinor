<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object;

use CuyZ\Valinor\Mapper\Object\Exception\CannotParseToDateTime;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;

/**
 * Can be given to {@see MapperBuilder::registerConstructor()} to describe which
 * date formats should be allowed during mapping.
 *
 * By default, if this constructor is never registered, the dates will accept
 * any valid timestamp or RFC 3339-formatted value.
 *
 * Usage:
 *
 * ```php
 * (new \CuyZ\Valinor\MapperBuilder())
 *     // Both `Cookie` and `ATOM` formats will be accepted
 *     ->registerConstructor(new DateTimeFormatConstructor(DATE_COOKIE, DATE_ATOM))
 *     ->mapper()
 *     ->map(DateTimeInterface::class, 'Monday, 08-Nov-1971 13:37:42 UTC');
 * ```
 *
 * @internal
 */
final class DateTimeFormatConstructor
{
    /** @var non-empty-array<non-empty-string> */
    private array $formats;

    /**
     * @param non-empty-string $format
     * @param non-empty-string ...$formats
     */
    public function __construct(string $format, string ...$formats)
    {
        $this->formats = [$format, ...$formats];
    }

    /**
     * @param class-string<DateTime|DateTimeImmutable> $className
     * @param non-empty-string|int|float $value
     */
    #[DynamicConstructor]
    public function __invoke(string $className, string|int|float $value): DateTimeInterface
    {
        foreach ($this->formats as $format) {
            $date = $className::createFromFormat($format, (string)$value) ?: null;

            if ($date) {
                return $date;
            }
        }

        throw new CannotParseToDateTime($this->formats);
    }
}
