<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;

final class ReadonlyMappingTest extends IntegrationTestCase
{
    public function test_single_property_and_constructor_parameter_are_mapped_properly(): void
    {
        $class = new class () {
            public readonly string $value; // @phpstan-ignore-line
        };

        try {
            $object = $this->mapperBuilder()->mapper()->map($class::class, 'foo');
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $object->value);
    }
}
