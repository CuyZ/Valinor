<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Configurator;

use Attribute;
use CuyZ\Valinor\Mapper\AsConverter;
use CuyZ\Valinor\Mapper\Tree\Message\MessageBuilder;
use DateTimeImmutable;

/**
 * Parses the input string using the given date format before mapping. This is
 * useful when the input data carries a date in a specific format that the
 * mapper would not otherwise recognize.
 *
 * The format must follow the syntax supported by
 * {@see DateTimeImmutable::createFromFormat()}. A value that does not match the
 * given format raises a mapping error.
 *
 * ```
 * use CuyZ\Valinor\MapperBuilder;
 * use CuyZ\Valinor\Mapper\Configurator\MapToDateTimeFromFormat;
 * use DateTimeInterface;
 *
 * final readonly class Event
 * {
 *     public function __construct(
 *         public string $name,
 *
 *         #[MapToDateTimeFromFormat('d/m/Y')]
 *         public DateTimeInterface $date,
 *     ) {}
 * }
 *
 * $event = (new MapperBuilder())
 *     ->mapper()
 *     ->map(Event::class, [
 *         'name' => 'Release of legendary album',
 *         'date' => '08/11/1971', // mapped to a `DateTimeImmutable`
 *     ]);
 * ```
 *
 * @api
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
#[AsConverter]
final class MapToDateTimeFromFormat
{
    public function __construct(
        /** @var non-empty-string */
        private string $format,
    ) {}

    public function map(string $value): DateTimeImmutable
    {
        $date = DateTimeImmutable::createFromFormat($this->format, $value);

        if ($date === false) {
            throw MessageBuilder::newError('Value {source_value} does not match the date format {format}.')
                ->withParameter('format', "`$this->format`")
                ->withCode('cannot_parse_datetime_format')
                ->build();
        }

        return $date;
    }
}
