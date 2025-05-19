<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Normalizer\CommonExamples;

use Attribute;
use CuyZ\Valinor\Normalizer\Format;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;
use DateTimeImmutable;
use DateTimeInterface;

final class DateFormatFromAttributeTest extends IntegrationTestCase
{
    public function test_date_format_attribute_works_properly(): void
    {
        $result = $this->normalizerBuilder()
            ->registerTransformer(DateTimeFormat::class)
            ->normalizer(Format::array())
            ->normalize(new class (
                eventName: 'Release of legendary album',
                date: new DateTimeImmutable('1971-11-08'),
            ) {
                public function __construct(
                    public string $eventName,
                    #[DateTimeFormat('Y/m/d')]
                    public DateTimeInterface $date,
                ) {}
            });

        self::assertSame([
            'eventName' => 'Release of legendary album',
            'date' => '1971/11/08',
        ], $result);
    }
}

#[Attribute(Attribute::TARGET_PROPERTY)]
final class DateTimeFormat
{
    public function __construct(private string $format) {}

    public function normalize(DateTimeInterface $date): string
    {
        return $date->format($this->format);
    }
}
