<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Object;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Tests\Integration\IntegrationTest;
use DateTimeInterface;

final class DateTimeMappingTest extends IntegrationTest
{
    public function test_default_datetime_constructor_cannot_be_used(): void
    {
        try {
            (new MapperBuilder())
                ->mapper()
                ->map(DateTimeInterface::class, ['datetime' => '2022/08/05', 'timezone' => 'Europe/Paris']);
        } catch (MappingError $exception) {
            $error = $exception->node()->messages()[0];

            self::assertSame('1607027306', $error->code());
        }
    }

    public function test_default_date_constructor_with_valid_rfc_3339_format_source_returns_datetime(): void
    {
        try {
            $result = (new MapperBuilder())
                ->mapper()
                ->map(DateTimeInterface::class, '2022-08-05T08:32:06+00:00');
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('2022-08-05T08:32:06+00:00', $result->format(DATE_ATOM));
    }

    public function test_default_date_constructor_with_valid_rfc_3339_and_milliseconds_format_source_returns_datetime(): void
    {
        try {
            $result = (new MapperBuilder())
                ->mapper()
                ->map(DateTimeInterface::class, '2022-08-05T08:32:06.123Z');
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('1659688326', $result->format('U'));
        self::assertSame('123', $result->format('v'));
        self::assertSame('123000', $result->format('u'));
    }

    public function test_default_date_constructor_with_valid_rfc_3339_and_microseconds_format_source_returns_datetime(): void
    {
        try {
            $result = (new MapperBuilder())
                ->mapper()
                ->map(DateTimeInterface::class, '2022-08-05T08:32:06.123456Z');
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('1659688326', $result->format('U'));
        self::assertSame('123', $result->format('v'));
        self::assertSame('123456', $result->format('u'));
    }

    public function test_default_date_constructor_with_valid_timestamp_format_source_returns_datetime(): void
    {
        try {
            $result = (new MapperBuilder())
                ->mapper()
                ->map(DateTimeInterface::class, 1659688380);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('1659688380', $result->format('U'));
    }

    public function test_default_date_constructor_with_timestamp_at_0_source_returns_datetime(): void
    {
        try {
            $result = (new MapperBuilder())
                ->mapper()
                ->map(DateTimeInterface::class, 0);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('0', $result->format('U'));
    }

    public function test_default_date_constructor_with_a_negative_timestamp_source_returns_datetime(): void
    {
        try {
            $result = (new MapperBuilder())
                ->mapper()
                ->map(DateTimeInterface::class, -1);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('-1', $result->format('U'));
    }

    public function test_registered_date_constructor_with_valid_source_returns_datetime(): void
    {
        try {
            $result = (new MapperBuilder())
                ->supportDateFormats('d/m/Y', 'Y/m/d')
                ->mapper()
                ->map(DateTimeInterface::class, '2022/08/05');
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('2022/08/05', $result->format('Y/m/d'));
    }

    public function test_default_date_constructor_with_invalid_source_throws_exception(): void
    {
        try {
            (new MapperBuilder())
                ->mapper()
                ->map(DateTimeInterface::class, 'invalid datetime');
        } catch (MappingError $exception) {
            $error = $exception->node()->messages()[0];

            self::assertSame('1630686564', $error->code());
            self::assertSame("Value 'invalid datetime' does not match any of the following formats: `Y-m-d\TH:i:sP`, `Y-m-d\TH:i:s.uP`, `U`.", (string)$error);
        }
    }

    public function test_registered_date_constructor_with_invalid_source_throws_exception(): void
    {
        try {
            (new MapperBuilder())
                ->supportDateFormats('Y/m/d')
                ->mapper()
                ->map(DateTimeInterface::class, 'invalid datetime');
        } catch (MappingError $exception) {
            $error = $exception->node()->messages()[0];

            self::assertSame('1630686564', $error->code());
            self::assertSame("Value 'invalid datetime' does not match any of the following formats: `Y/m/d`.", (string)$error);
        }
    }

    public function test_date_constructor_with_overridden_format_source_throws_exception(): void
    {
        try {
            (new MapperBuilder())
                ->supportDateFormats('Y/m/d')
                ->supportDateFormats('d/m/Y')
                ->mapper()
                ->map(DateTimeInterface::class, '1971-11-08');
        } catch (MappingError $exception) {
            $error = $exception->node()->messages()[0];

            self::assertSame('1630686564', $error->code());
            self::assertSame("Value '1971-11-08' does not match any of the following formats: `d/m/Y`.", (string)$error);
        }
    }
}
