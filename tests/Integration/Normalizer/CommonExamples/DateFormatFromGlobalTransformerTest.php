<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Normalizer\CommonExamples;

use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Normalizer\Format;
use DateTimeImmutable;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;

final class DateFormatFromGlobalTransformerTest extends TestCase
{
    public function test_date_format_from_global_transformer_works_properly(): void
    {
        $result = (new MapperBuilder())
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
