<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Object;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Tests\Integration\IntegrationTest;
use DateTimeZone;

final class DateTimeZoneMappingTest extends IntegrationTest
{
    public function test_can_map_to_timezone_with_default_constructor(): void
    {
        try {
            $result = (new MapperBuilder())->mapper()->map(DateTimeZone::class, 'Europe/Paris');
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('Europe/Paris', $result->getName());
    }

    public function test_constructor_with_one_argument_replaces_default_constructor(): void
    {
        try {
            $result = (new MapperBuilder())
                ->registerConstructor(
                    fn (string $europeanCity): DateTimeZone => new DateTimeZone("Europe/$europeanCity")
                )
                ->mapper()
                ->map(DateTimeZone::class, 'Paris');
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('Europe/Paris', $result->getName());
    }

    public function test_constructor_with_two_arguments_does_not_replaces_default_constructor(): void
    {
        $mapper = (new MapperBuilder())->registerConstructor(
            fn (string $continent, string $city): DateTimeZone => new DateTimeZone("$continent/$city")
        )->mapper();

        try {
            $result = $mapper->map(DateTimeZone::class, 'Europe/Paris');

            self::assertSame('Europe/Paris', $result->getName());
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        try {
            $result = $mapper->map(DateTimeZone::class, [
                'continent' => 'Europe',
                'city' => 'Paris',
            ]);

            self::assertSame('Europe/Paris', $result->getName());
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }
    }

    public function test_invalid_timezone_throws_exception(): void
    {
        try {
            (new MapperBuilder())->mapper()->map(DateTimeZone::class, 'Jupiter/Europa');
        } catch (MappingError $exception) {
            $error = $exception->node()->messages()[0];

            self::assertSame("Value 'Jupiter/Europa' is not a valid timezone.", $error->toString());
        }
    }
}
