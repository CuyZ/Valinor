<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Object;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Object\DateTimeObjectBuilder;
use CuyZ\Valinor\Tests\Integration\IntegrationTest;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;

use function mt_rand;

final class DateTimeMappingTest extends IntegrationTest
{
    public function test_datetime_properties_are_converted_properly(): void
    {
        $dateTimeInterfaceValue = $this->get_random_timestamp();
        $dateTimeImmutableValue = $this->get_random_timestamp();
        $dateTimeFromTimestampValue = $this->get_random_timestamp();
        $dateTimeFromTimestampWithFormatValue = (new DateTime())->setTimestamp($this->get_random_timestamp())->format(DATE_ATOM);
        $dateTimeFromArrayValue = (new DateTime())->setTimestamp($this->get_random_timestamp())->format('Y-m-d H:i:s');
        $mysqlDateValue = (new DateTime())->setTimestamp($this->get_random_timestamp())->format('Y-m-d H:i:s');
        $pgsqlDateValue = (new DateTime())->setTimestamp($this->get_random_timestamp())->format('Y-m-d H:i:s.u');

        $dateTimeInterface = new DateTimeImmutable(sprintf('@%s', $dateTimeInterfaceValue));
        $dateTimeImmutable = new DateTimeImmutable(sprintf('@%s', $dateTimeImmutableValue));
        $dateTimeFromTimestamp = $this->get_random_timestamp();
        $dateTimeFromTimestampWithFormat = [
            'datetime' => $dateTimeFromTimestampValue,
            'format' => 'U',
        ];
        $dateTimeFromAtomFormat = $dateTimeFromTimestampWithFormatValue;
        $dateTimeFromArray = [
            'datetime' => $dateTimeFromArrayValue,
            'format' => 'Y-m-d H:i:s',
        ];
        $mysqlDate = $mysqlDateValue;
        $pgsqlDate = $pgsqlDateValue;

        try {
            $result = $this->mapperBuilder->mapper()->map(AllDateTimeValues::class, [
                'dateTimeInterface' => $dateTimeInterface,
                'dateTimeImmutable' => $dateTimeImmutable,
                'dateTimeFromTimestamp' => $dateTimeFromTimestamp,
                'dateTimeFromTimestampWithFormat' => $dateTimeFromTimestampWithFormat,
                'dateTimeFromAtomFormat' => $dateTimeFromAtomFormat,
                'dateTimeFromArray' => $dateTimeFromArray,
                'mysqlDate' => $mysqlDate,
                'pgsqlDate' => $pgsqlDate,

            ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertInstanceOf(DateTimeImmutable::class, $result->dateTimeInterface);
        self::assertEquals($dateTimeInterface, $result->dateTimeInterface);
        self::assertEquals($dateTimeImmutable, $result->dateTimeImmutable);
        self::assertEquals(new DateTimeImmutable("@$dateTimeFromTimestamp"), $result->dateTimeFromTimestamp);
        self::assertEquals(new DateTimeImmutable("@{$dateTimeFromTimestampWithFormat['datetime']}"), $result->dateTimeFromTimestampWithFormat);
        self::assertEquals(DateTimeImmutable::createFromFormat(DATE_ATOM, $dateTimeFromAtomFormat), $result->dateTimeFromAtomFormat);
        self::assertEquals(DateTimeImmutable::createFromFormat($dateTimeFromArray['format'], $dateTimeFromArray['datetime']), $result->dateTimeFromArray);
        self::assertEquals(DateTimeImmutable::createFromFormat(DateTimeObjectBuilder::DATE_MYSQL, $mysqlDate), $result->mysqlDate);
        self::assertEquals(DateTimeImmutable::createFromFormat(DateTimeObjectBuilder::DATE_PGSQL, $pgsqlDate), $result->pgsqlDate);
    }

    public function test_invalid_datetime_throws_exception(): void
    {
        try {
            $this->mapperBuilder
                ->mapper()
                ->map(SimpleDateTimeValues::class, [
                    'dateTime' => 'invalid datetime',
                ]);
        } catch (MappingError $exception) {
            $error = $exception->node()->children()['dateTime']->messages()[0];

            self::assertSame('1630686564', $error->code());
            self::assertSame('Impossible to convert `invalid datetime` to `DateTime`.', (string)$error);
        }
    }

    public function test_invalid_datetime_from_array_throws_exception(): void
    {
        try {
            $this->mapperBuilder
                ->mapper()
                ->map(SimpleDateTimeValues::class, [
                    'dateTime' => [
                        'datetime' => 1337,
                        'format' => 'H',
                    ],
                ]);
        } catch (MappingError $exception) {
            $error = $exception->node()->children()['dateTime']->messages()[0];

            self::assertSame('1630686564', $error->code());
            self::assertSame('Impossible to convert `1337` to `DateTime`.', (string)$error);
        }
    }

    public function test_invalid_array_source_throws_exception(): void
    {
        try {
            $this->mapperBuilder
                ->mapper()
                ->map(SimpleDateTimeValues::class, [
                    'dateTime' => [
                        'invalid key' => '2012-12-21T13:37:42+00:00',
                    ],
                ]);
        } catch (MappingError $exception) {
            $error = $exception->node()->children()['dateTime']->children()['value']->messages()[0];

            self::assertSame('1607027306', $error->code());
        }
    }

    private function get_random_timestamp(): int
    {
        return mt_rand(1, time());
    }
}

final class SimpleDateTimeValues
{
    public DateTimeInterface $dateTimeInterface;

    public DateTimeImmutable $dateTimeImmutable;

    public DateTime $dateTime;
}

final class AllDateTimeValues
{
    public DateTimeInterface $dateTimeInterface;

    public DateTimeImmutable $dateTimeImmutable;

    public DateTime $dateTimeFromTimestamp;

    public DateTime $dateTimeFromTimestampWithFormat;

    public DateTimeInterface $dateTimeFromAtomFormat;

    public DateTimeInterface $dateTimeFromArray;

    public DateTimeInterface $mysqlDate;

    public DateTimeInterface $pgsqlDate;
}
