<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Object;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Object\BackwardCompatibilityDateTimeConstructor;
use CuyZ\Valinor\Mapper\TreeMapper;
use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Tests\Integration\IntegrationTest;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;

use function random_int;

final class BackwardCompatibilityDateTimeMappingTest extends IntegrationTest
{
    private TreeMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mapper = (new MapperBuilder())
            ->registerConstructor(new BackwardCompatibilityDateTimeConstructor())
            ->mapper();
    }

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
            $result = $this->mapper->map(AllDateTimeValues::class, [
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
        self::assertEquals(DateTimeImmutable::createFromFormat(BackwardCompatibilityDateTimeConstructor::DATE_MYSQL, $mysqlDate), $result->mysqlDate);
        self::assertEquals(DateTimeImmutable::createFromFormat(BackwardCompatibilityDateTimeConstructor::DATE_PGSQL, $pgsqlDate), $result->pgsqlDate);
        self::assertSame($sqlDateNotTime . ' 00:00:00', $result->sqlDateNotTime->format(BackwardCompatibilityDateTimeConstructor::DATE_MYSQL));
    }

    public function test_invalid_datetime_throws_exception(): void
    {
        try {
            $this->mapper->map(DateTimeInterface::class, 'invalid datetime');
        } catch (MappingError $exception) {
            $error = $exception->node()->messages()[0];

            self::assertSame('1659706547', $error->code());
            self::assertSame("Value 'invalid datetime' does not match a valid date format.", (string)$error);
        }
    }

    public function test_invalid_datetime_from_array_throws_exception(): void
    {
        try {
            $this->mapper->map(DateTimeInterface::class, [
                'datetime' => 1337,
                'format' => 'H',
            ]);
        } catch (MappingError $exception) {
            $error = $exception->node()->messages()[0];

            self::assertSame('1659706547', $error->code());
            self::assertSame("Value 1337 does not match a valid date format.", (string)$error);
        }
    }

    public function test_invalid_array_source_throws_exception(): void
    {
        try {
            $this->mapper->map(DateTimeInterface::class, [
                'dateTime' => [
                    'invalid key' => '2012-12-21T13:37:42+00:00',
                ],
            ]);
        } catch (MappingError $exception) {
            $error = $exception->node()->children()['value']->messages()[0];

            self::assertSame('1607027306', $error->code());
        }
    }

    private function buildRandomTimestamp(): int
    {
        return random_int(1, 32503726800);
    }
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
