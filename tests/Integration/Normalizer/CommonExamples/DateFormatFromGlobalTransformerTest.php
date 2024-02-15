<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Normalizer\CommonExamples;

use CuyZ\Valinor\Normalizer\Format;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;
use DateTimeImmutable;
use DateTimeInterface;

final class DateFormatFromGlobalTransformerTest extends IntegrationTestCase
{
    public function test_date_format_from_global_transformer_works_properly(): void
    {
        $result = $this->mapperBuilder()
            ->registerTransformer(
                fn (DateTimeInterface $date) => $date->format('Y/m/d'),
            )
            ->normalizer(Format::array())
            ->normalize(new class (
                eventName: 'Release of legendary album',
                date: new DateTimeImmutable('1971-11-08'),
            ) {
                public function __construct(
                    public string $eventName,
                    public DateTimeInterface $date,
                ) {}
            });

        self::assertSame([
            'eventName' => 'Release of legendary album',
            'date' => '1971/11/08',
        ], $result);
    }
}
