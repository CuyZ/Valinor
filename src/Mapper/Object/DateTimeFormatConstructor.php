<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object;

use CuyZ\Valinor\Mapper\Object\Exception\CannotParseToDateTime;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;

/** @internal */
final class DateTimeFormatConstructor
{
    /** @var non-empty-list<non-empty-string> */
    private array $formats;

    /**
     * @no-named-arguments
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
