<?php

namespace CuyZ\Valinor\Tests\Integration\Normalizer\Configurator;

use CuyZ\Valinor\Normalizer\Configurator\ConvertDateTime;
use CuyZ\Valinor\Normalizer\Format;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;
use DateTimeInterface;
use DateTimeZone;
use PHPUnit\Framework\Attributes\DataProvider;
use Safe\DateTimeImmutable;

final class ConvertDateTimeTest extends IntegrationTestCase
{
    /**
     * @param non-empty-string $expectedValue
     * @param non-empty-string $format
     */
    #[DataProvider('date_time_data_provider')]
    public function test_date_time_converts_values(string $expectedValue, string $format, object $value): void
    {
        $result = $this->normalizerBuilder()
            ->configureWith(new ConvertDateTime($format))
            ->normalizer(Format::array())
            ->normalize($value);

        self::assertSame($expectedValue, $result);
    }

    public static function date_time_data_provider(): iterable
    {
        $date = new DateTimeImmutable('1955-11-05T21:10:14', new DateTimeZone('UTC'));

        yield 'to ATOM format' => [
            'expectedValue' => '1955-11-05T21:10:14+00:00',
            'format' => DateTimeInterface::ATOM,
            'value' => $date,
        ];

        yield 'to RFC1036 format' => [
            'expectedValue' => 'Sat, 05 Nov 55 21:10:14 +0000',
            'format' => DateTimeInterface::RFC1036,
            'value' => $date,
        ];

        yield 'to custom format' => [
            'expectedValue' => '05.11.1955 21:10:14',
            'format' => 'd.m.Y H:i:s',
            'value' => $date,
        ];
    }
}
