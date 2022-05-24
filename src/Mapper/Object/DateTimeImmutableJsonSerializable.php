<?php

namespace CuyZ\Valinor\Mapper\Object;

use DateTimeZone;

/** @internal */
final class DateTimeImmutableJsonSerializable extends \DateTimeImmutable implements \JsonSerializable
{
    private string $jsonFormat;

    public function __construct(string $datetime = 'now', DateTimeZone $timezone = null, string $jsonFormat = DATE_RFC3339)
    {
        parent::__construct($datetime, $timezone);
        $this->jsonFormat = $jsonFormat;
    }

    public static function createFromFormat($format, $datetime, ?DateTimeZone $timezone = null)
    {
        return new self($datetime, $timezone, $format);
    }

    public function jsonSerialize()
    {
        $dateString = $this->format($this->jsonFormat);
        return (preg_match("/^\d+$/", $dateString)) ? (int)($dateString) : $dateString;
    }
}
