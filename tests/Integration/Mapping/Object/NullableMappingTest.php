<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Object;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;

final class NullableMappingTest extends IntegrationTestCase
{
    public function test_nullable_properties_default_value_are_handled_properly(): void
    {
        try {
            $result = (new MapperBuilder())->mapper()->map(NullablePropertyWithNullDefaultValue::class, []);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(null, $result->nullableWithNull);
        self::assertSame('foo', $result->nullableWithString);
    }
}

final class NullablePropertyWithNullDefaultValue
{
    public ?string $nullableWithNull = null;

    public ?string $nullableWithString = 'foo';
}
