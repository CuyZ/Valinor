<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Normalizer\CommonExamples;

use Attribute;
use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Normalizer\Format;
use DateTimeImmutable;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;

final class DateFormatFromAttributeTest extends TestCase
{
    public function test_date_format_attribute_works_properly(): void
    {
        $result = (new MapperBuilder())
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
