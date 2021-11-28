<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Integration\IntegrationTest;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use stdClass;

use function get_class;

final class ObjectBindingMappingTest extends IntegrationTest
{
    public function test_bind_object_binds_object(): void
    {
        $object = new stdClass();
        $class = new class () {
            public stdClass $object;
        };

        try {
            $result = $this->mapperBuilder
                ->bind(fn (): stdClass => $object)
                ->mapper()
                ->map(get_class($class), [
                    'object' => new stdClass(),
                ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame($object, $result->object);
    }

    public function test_bind_object_with_docblock_parameter_binds_object(): void
    {
        $object = new stdClass();
        $class = new class () {
            public stdClass $object;
        };

        try {
            $result = $this->mapperBuilder
                ->bind(/** @return stdClass */ fn () => $object)
                ->mapper()
                ->map(get_class($class), [
                    'object' => new stdClass(),
                ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame($object, $result->object);
    }

    public function test_bind_datetime_binds_datetime(): void
    {
        $default = new DateTime('@1356097062');
        $defaultImmutable = new DateTimeImmutable('@1356097062');

        try {
            $result = $this->mapperBuilder
                ->bind(fn (): DateTime => $default)
                ->bind(fn (): DateTimeImmutable => $defaultImmutable)
                ->mapper()
                ->map(SimpleDateTimeValues::class, [
                    'dateTimeInterface' => 1357047105,
                    'dateTimeImmutable' => 1357047105,
                    'dateTime' => 1357047105,
                ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame($defaultImmutable, $result->dateTimeInterface);
        self::assertSame($defaultImmutable, $result->dateTimeImmutable);
        self::assertSame($default, $result->dateTime);
    }
}

final class SimpleDateTimeValues
{
    public DateTimeInterface $dateTimeInterface;

    public DateTimeImmutable $dateTimeImmutable;

    public DateTime $dateTime;
}
