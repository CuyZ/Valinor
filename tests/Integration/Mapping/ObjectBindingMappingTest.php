<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Integration\IntegrationTest;
use DateTime;
use DateTimeImmutable;
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
                ->map(get_class($class), []);
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
                ->map(get_class($class), []);
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
                    'dateTimeImmutable' => 1357047105,
                    'dateTime' => 1357047105,
                ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame($defaultImmutable, $result->dateTimeImmutable);
        self::assertSame($default, $result->dateTime);
    }

    public function test_bind_object_with_one_argument_binds_object(): void
    {
        try {
            $result = $this->mapperBuilder
                ->bind(function (int $int): stdClass {
                    $class = new stdClass();
                    $class->int = $int;

                    return $class;
                })
                ->mapper()
                ->map(stdClass::class, 1337);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(1337, $result->int);
    }

    public function test_bind_object_with_arguments_binds_object(): void
    {
        try {
            $result = $this->mapperBuilder
                ->bind(function (string $string, int $int, float $float = 1337.404): stdClass {
                    $class = new stdClass();
                    $class->string = $string;
                    $class->int = $int;
                    $class->float = $float;

                    return $class;
                })
                ->mapper()
                ->map(stdClass::class, [
                    'string' => 'foo',
                    'int' => 42,
                ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $result->string);
        self::assertSame(42, $result->int);
        self::assertSame(1337.404, $result->float);
    }
}

final class SimpleDateTimeValues
{
    public DateTimeImmutable $dateTimeImmutable;

    public DateTime $dateTime;
}
