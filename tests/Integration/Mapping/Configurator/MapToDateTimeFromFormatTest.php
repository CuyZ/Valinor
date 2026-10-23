<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Configurator;

use CuyZ\Valinor\Mapper\Configurator\MapToDateTimeFromFormat;
use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;
use DateTimeImmutable;
use DateTimeInterface;

final class MapToDateTimeFromFormatTest extends IntegrationTestCase
{
    public function test_attribute_maps_string_to_datetime_using_format(): void
    {
        $class = new class () {
            #[MapToDateTimeFromFormat('d/m/Y')]
            public DateTimeInterface $date;
        };

        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map($class::class, '08/11/1971');
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertInstanceOf(DateTimeImmutable::class, $result->date);
        self::assertSame('1971-11-08', $result->date->format('Y-m-d'));
    }

    public function test_attribute_maps_string_to_datetime_using_format_on_promoted_property(): void
    {
        $class = new class (new DateTimeImmutable()) {
            public function __construct(
                #[MapToDateTimeFromFormat('d/m/Y')]
                public DateTimeInterface $date,
            ) {}
        };

        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map($class::class, '08/11/1971');
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertInstanceOf(DateTimeImmutable::class, $result->date);
        self::assertSame('1971-11-08', $result->date->format('Y-m-d'));
    }

    public function test_attribute_maps_string_to_datetime_immutable_using_format(): void
    {
        $class = new class () {
            #[MapToDateTimeFromFormat('d/m/Y')]
            public DateTimeImmutable $date;
        };

        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map($class::class, '08/11/1971');
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('1971-11-08', $result->date->format('Y-m-d'));
    }

    public function test_attribute_maps_string_to_nullable_datetime_immutable_using_format(): void
    {
        $class = new class () {
            #[MapToDateTimeFromFormat('d/m/Y')]
            public DateTimeImmutable|null $date = null;
        };

        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map($class::class, '08/11/1971');
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertInstanceOf(DateTimeImmutable::class, $result->date);
        self::assertSame('1971-11-08', $result->date->format('Y-m-d'));
    }

    public function test_value_not_matching_format_raises_mapping_error(): void
    {
        $class = new class () {
            public string $name;

            #[MapToDateTimeFromFormat('d/m/Y')]
            public DateTimeInterface $date;
        };

        try {
            $this->mapperBuilder()
                ->mapper()
                ->map($class::class, ['name' => 'John Doe', 'date' => '1971-11-08']);

            self::fail('Expected a mapping error to be raised.');
        } catch (MappingError $error) {
            $message = $error->messages()->toArray()[0];

            self::assertSame('date', $message->path());
            self::assertSame('cannot_parse_datetime_format', $message->code());
        }
    }
}
