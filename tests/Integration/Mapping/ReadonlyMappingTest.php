<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Tests\Integration\IntegrationTest;
use CuyZ\Valinor\Tests\Integration\Mapping\Fixture\ReadonlyValues;

/**
 * @requires PHP >= 8.1
 */
final class ReadonlyMappingTest extends IntegrationTest
{
    public function test_single_property_and_constructor_parameter_are_mapped_properly(): void
    {
        try {
            $object = (new MapperBuilder())->mapper()->map(ReadonlyValues::class, [
                'value' => 'foo',
            ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $object->value);
    }
}
