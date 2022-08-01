<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Object;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Object\DateTimeObjectBuilder;
use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Tests\Integration\IntegrationTest;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;

use function random_int;

final class DateTimeMappingTest extends IntegrationTest
{
    public function test_datetime_properties_are_converted_properly(): void
    {
        $dateTimeInterface = new DateTimeImmutable('@' . $this->buildRandomTimestamp());
        $dateTimeImmutable = new DateTimeImmutable('@' . $this->buildRandomTimestamp());
        $dateTimeFromTimestamp = $this->buildRandomTimestamp();
        $dateTimeFromTimestampWithOutFormat = [
            'datetime' => $this->buildRandomTimestamp(),
        ];
        $dateTimeFromTimestampWithFormat = [
            'datetime' => $this->buildRandomTimestamp(),
            'format' => 'U',
        ];
        $dateTimeFromAtomFormat = (new DateTime())->setTimestamp($this->buildRandomTimestamp())->format(DATE_ATOM);
        $dateTimeFromArray = [
            'datetime' => (new DateTime('@' . $this->buildRandomTimestamp()))->format('Y-m-d H:i:s'),
            'format' => 'Y-m-d H:i:s',
        ];
        $mysqlDate = (new DateTime('@' . $this->buildRandomTimestamp()))->format('Y-m-d H:i:s');
        $pgsqlDate = (new DateTime('@' . $this->buildRandomTimestamp()))->format('Y-m-d H:i:s.u');

        $sqlDateNotTime = '2022-04-30';

        try {
            $result = (new MapperBuilder())->mapper()->map(AllDateTimeValues::class, [
                'dateTimeInterface' => $dateTimeInterface,
                'dateTimeImmutable' => $dateTimeImmutable,
                'dateTimeFromTimestamp' => $dateTimeFromTimestamp,
                'dateTimeFromTimestampWithOutFormat' => $dateTimeFromTimestampWithOutFormat,
                'dateTimeFromTimestampWithFormat' => $dateTimeFromTimestampWithFormat,
                'dateTimeFromAtomFormat' => $dateTimeFromAtomFormat,
                'dateTimeFromArray' => $dateTimeFromArray,
                'mysqlDate' => $mysqlDate,
                'pgsqlDate' => $pgsqlDate,
                'sqlDateNotTime' => $sqlDateNotTime,

            ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertInstanceOf(DateTimeImmutable::class, $result->dateTimeInterface);
        self::assertEquals($dateTimeInterface, $result->dateTimeInterface);
        self::assertEquals($dateTimeImmutable, $result->dateTimeImmutable);
        self::assertEquals(new DateTimeImmutable("@$dateTimeFromTimestamp"), $result->dateTimeFromTimestamp);
        self::assertEquals(new DateTimeImmutable("@{$dateTimeFromTimestampWithFormat['datetime']}"), $result->dateTimeFromTimestampWithFormat);
        self::assertEquals(new DateTimeImmutable("@{$dateTimeFromTimestampWithOutFormat['datetime']}"), $result->dateTimeFromTimestampWithOutFormat);
        self::assertEquals(DateTimeImmutable::createFromFormat(DATE_ATOM, $dateTimeFromAtomFormat), $result->dateTimeFromAtomFormat);
        self::assertEquals(DateTimeImmutable::createFromFormat($dateTimeFromArray['format'], $dateTimeFromArray['datetime']), $result->dateTimeFromArray);
        self::assertEquals(DateTimeImmutable::createFromFormat(DateTimeObjectBuilder::DATE_MYSQL, $mysqlDate), $result->mysqlDate);
        self::assertEquals(DateTimeImmutable::createFromFormat(DateTimeObjectBuilder::DATE_PGSQL, $pgsqlDate), $result->pgsqlDate);
        self::assertSame($sqlDateNotTime . ' 00:00:00', $result->sqlDateNotTime->format(DateTimeObjectBuilder::DATE_MYSQL));
    }

    public function test_invalid_datetime_throws_exception(): void
    {
        try {
            (new MapperBuilder())
                ->mapper()
                ->map(DateTimeInterface::class, 'invalid datetime');
        } catch (MappingError $exception) {
            $error = $exception->node()->messages()[0];

            self::assertSame('1630686564', $error->code());
            self::assertSame("Value 'invalid datetime' does not match a valid date format.", (string)$error);
        }
    }

    public function test_invalid_datetime_from_array_throws_exception(): void
    {
        try {
            (new MapperBuilder())
                ->mapper()
                ->map(DateTimeInterface::class, [
                    'datetime' => 1337,
                    'format' => 'H',
                ]);
        } catch (MappingError $exception) {
            $error = $exception->node()->messages()[0];

            self::assertSame('1630686564', $error->code());
            self::assertSame("Value 1337 does not match a valid date format.", (string)$error);
        }
    }

    public function test_invalid_array_source_throws_exception(): void
    {
        try {
            (new MapperBuilder())
                ->mapper()
                ->map(DateTimeInterface::class, [
                    'dateTime' => [
                        'invalid key' => '2012-12-21T13:37:42+00:00',
                    ],
                ]);
        } catch (MappingError $exception) {
            $error = $exception->node()->children()['value']->messages()[0];

            self::assertSame('1607027306', $error->code());
        }
    }

    public function test_will_only_map_provided_formats(): void
    {
        $mapper = (new MapperBuilder())
            ->withDateTimeFormats([DateTimeInterface::RFC3339])
            ->mapper();

        $result = $mapper->map(DateTimeImmutableValue::class, [
            'value' => '2022-08-01T00:00:00+00:00',
        ]);

        self::assertInstanceOf(DateTimeImmutableValue::class, $result);
        self::assertSame('2022-08-01T00:00:00+00:00', $result->value->format(DateTimeInterface::RFC3339));

        try {
            $mapper->map(DateTimeImmutableValue::class, [
                'value' => '2022-08-01'
            ]);
            self::fail('Mapper should throw an exception due to the fact that we did not explicitly allowed the MYSQL_DATE format to be parsed.');
        } catch (MappingError $exception) {
            $error = $exception->node()->children()['value']->messages()[0];

            self::assertStringContainsString('Value \'2022-08-01\' does not match a valid date format.', (string) $error);
        }
    }

    private function buildRandomTimestamp(): int
    {
        return random_int(1, 32503726800);
    }
}

final class DateTimeImmutableValue
{
    public DateTimeImmutable $value;
}

final class AllDateTimeValues
{
    public DateTimeInterface $dateTimeInterface;

    public DateTimeImmutable $dateTimeImmutable;

    public DateTime $dateTimeFromTimestamp;

    public DateTime $dateTimeFromTimestampWithOutFormat;

    public DateTime $dateTimeFromTimestampWithFormat;

    public DateTimeInterface $dateTimeFromAtomFormat;

    public DateTimeInterface $dateTimeFromArray;

    public DateTimeInterface $mysqlDate;

    public DateTimeInterface $pgsqlDate;

    public DateTimeImmutable $sqlDateNotTime;
}
