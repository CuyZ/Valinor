<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Converter\CommonExamples;

use Attribute;
use CuyZ\Valinor\Mapper\AsConverter;
use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;
use DateTimeImmutable;
use DateTimeInterface;
use RuntimeException;

final class DateTimeFormatFromAttributeMappingTest extends IntegrationTestCase
{
    public function test_can_use_datetime_format_converter_attribute(): void
    {
        $class = new class () {
            #[DateTimeFormat('Y-m-d', 'Y/m/d')]
            public DateTimeInterface $value;
        };

        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map($class::class, '1971/11/08');

            self::assertSame('1971-11-08', $result->value->format('Y-m-d'));
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }
    }
}

#[Attribute, AsConverter]
final class DateTimeFormat
{
    /** @var non-empty-list<non-empty-string> */
    private array $formats;

    /**
     * @no-named-arguments
     * @param non-empty-string $format
     * @param non-empty-string ...$otherFormats
     */
    public function __construct(string $format, string ...$otherFormats)
    {
        $this->formats = [$format, ...$otherFormats];
    }

    public function map(string $value): DateTimeInterface
    {
        foreach ($this->formats as $format) {
            $date = DateTimeImmutable::createFromFormat($format, $value);

            if ($date !== false) {
                return $date;
            }
        }

        throw new RuntimeException("Invalid datetime value `$value`");
    }
}
