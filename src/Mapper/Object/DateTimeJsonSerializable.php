<?php

namespace CuyZ\Valinor\Mapper\Object;

use DateTimeZone;
use Exception;
use Throwable;

/** @internal */
final class DateTimeJsonSerializable extends \DateTime implements \JsonSerializable
{
    private string $jsonFormat;

    public function __construct(string $datetime = 'now', DateTimeZone $timezone = null, string $jsonFormat = DATE_RFC3339)
    {
        parent::__construct($datetime, $timezone);
        $this->jsonFormat = $jsonFormat;
    }

    /**
     * @param string $format
     * @param string $datetime
     * @return DateTimeJsonSerializable|false
     * @throws Exception
     */
    public static function createFromFormat($format, $datetime, ?DateTimeZone $timezone = null)
    {
        try {
            return new self($datetime, $timezone, $format);
        } catch (Throwable $exception) {
            return false;
        }
    }

    /**
     * @return int|string
     */
    public function jsonSerialize()
    {
        $dateString = $this->format($this->jsonFormat);
        return (preg_match("/^(([1-9]\d*)|0)$/", $dateString)) ? (int)($dateString) : $dateString;
    }
}
