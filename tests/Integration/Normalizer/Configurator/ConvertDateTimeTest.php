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
     * @param array<string, non-empty-string> $expectedValue
     * @param non-empty-string $format
     */
    #[DataProvider('date_time_data_provider')]
    public function test_date_time_converts_values(array $expectedValue, string $format, object $object): void
    {
        $result = $this->normalizerBuilder()
                ->configureWith(new ConvertDateTime($format))
                ->normalizer(Format::array())
                ->normalize($object);

        self::assertSame($expectedValue, $result);
    }

    /**
     * @return iterable<string, array{array, non-empty-string, object}>
     */
    public static function date_time_data_provider(): iterable
    {
        $object = new class () {
            public DateTimeInterface $someDateTime;
        };
        $object->someDateTime = new DateTimeImmutable('1955-11-05T21:10:14', new DateTimeZone('UTC'));

        yield 'to ATOM format' => [['someDateTime' => '1955-11-05T21:10:14+00:00'], DateTimeInterface::ATOM, $object];
        yield 'to RFC1036 format' => [['someDateTime' => 'Sat, 05 Nov 55 21:10:14 +0000'], DateTimeInterface::RFC1036, $object];
        yield 'to custom format' => [['someDateTime' => '05.11.1955 21:10:14'], 'd.m.Y H:i:s', $object];
    }
}
