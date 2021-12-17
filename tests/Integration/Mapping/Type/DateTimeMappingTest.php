<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Type;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Object\DateTimeObjectBuilder;
use CuyZ\Valinor\Mapper\Object\Exception\CannotParseToDateTime;
use CuyZ\Valinor\Tests\Integration\IntegrationTest;
use CuyZ\Valinor\Type\Resolver\Exception\UnionTypeDoesNotAllowNull;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;

final class DateTimeMappingTest extends IntegrationTest
{
    public function test_datetime_properties_are_converted_properly(): void
    {
        $dateTimeInterface = new DateTimeImmutable('@1356097062');
        $dateTimeImmutable = new DateTimeImmutable('@1356097062');
        $dateTimeFromTimestamp = 1356097062;
        $dateTimeFromTimestampWithFormat = [
            'datetime' => 1356097062,
            'format' => 'U',
        ];
        $dateTimeFromAtomFormat = '2012-12-21T13:37:42+00:00';
        $dateTimeFromArray = [
            'datetime' => '2012-12-21 13:37:42',
            'format' => 'Y-m-d H:i:s',
        ];
        $mysqlTimestamp = '2012-12-21 13:37:42';
        $pgsqlDateTime = '2012-12-21 13:37:42.123456';

        try {
            $result = $this->mapperBuilder->mapper()->map(AllDateTimeValues::class, [
                'dateTimeInterface' => $dateTimeInterface,
                'dateTimeImmutable' => $dateTimeImmutable,
                'dateTimeFromTimestamp' => $dateTimeFromTimestamp,
                'dateTimeFromTimestampWithFormat' => $dateTimeFromTimestampWithFormat,
                'dateTimeFromAtomFormat' => $dateTimeFromAtomFormat,
                'dateTimeFromArray' => $dateTimeFromArray,
                'mysqlTimestamp' => $mysqlTimestamp,
                'pgsqlDateTime' => $pgsqlDateTime,

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
        self::assertEquals(DateTimeImmutable::createFromFormat(DateTimeObjectBuilder::DATE_MYSQL, $mysqlTimestamp), $result->mysqlTimestamp);
        self::assertEquals(DateTimeImmutable::createFromFormat(DateTimeObjectBuilder::DATE_PGSQL, $pgsqlDateTime), $result->pgsqlDateTime);
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
            $error = $exception->describe()['dateTime'][0];

            self::assertInstanceOf(CannotParseToDateTime::class, $error);
            self::assertSame(1630686564, $error->getCode());
            self::assertSame('Impossible to convert `invalid datetime` to `DateTime`.', $error->getMessage());
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
            $error = $exception->describe()['dateTime'][0];

            self::assertInstanceOf(CannotParseToDateTime::class, $error);
            self::assertSame(1630686564, $error->getCode());
            self::assertSame('Impossible to convert `1337` to `DateTime`.', $error->getMessage());
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
            $error = $exception->describe()['dateTime.datetime'][0];

            self::assertInstanceOf(UnionTypeDoesNotAllowNull::class, $error);
        }
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

    public DateTimeInterface $mysqlTimestamp;

    public DateTimeInterface $pgsqlDateTime;
}
