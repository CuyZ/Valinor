<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Object;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;

final class NullableMappingTest extends IntegrationTestCase
{
    public function test_nullable_properties_default_value_are_handled_properly(): void
    {
        try {
            $result = $this->mapperBuilder()->mapper()->map(NullablePropertyWithNullDefaultValue::class, []);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(null, $result->nullableWithNull);
        self::assertSame('foo', $result->nullableWithString);
    }

    public function test_null_value_mapped_in_null_is_handled_properly(): void
    {
        try {
            $result = $this->mapperBuilder()->mapper()->map('null', null);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(null, $result); // @phpstan-ignore-line
    }

    public function test_string_value_mapped_in_null_throws_exception(): void
    {
        try {
            $this->mapperBuilder()->mapper()->map('null', 'foo');

            self::fail('No mapping error when one was expected');
        } catch (MappingError $exception) {
            $error = $exception->node()->messages()[0];

            self::assertSame('1710263908', $error->code());
            self::assertSame("Value 'foo' is not null.", (string)$error);
        }
    }
}

final class NullablePropertyWithNullDefaultValue
{
    public ?string $nullableWithNull = null;

    public ?string $nullableWithString = 'foo';
}
