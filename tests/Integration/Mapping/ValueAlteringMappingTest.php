<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Integration\IntegrationTest;
use CuyZ\Valinor\Tests\Integration\Mapping\Fixture\SimpleObject;

use function strtolower;
use function strtoupper;

final class ValueAlteringMappingTest extends IntegrationTest
{
    public function test_alter_string_alters_value(): void
    {
        try {
            $result = $this->mapperBuilder
                ->alter(fn () => 'bar')
                ->alter(fn (string $value) => strtolower($value))
                ->alter(fn (string $value) => strtoupper($value))
                ->alter(/** @param string $value */ fn ($value) => $value . '!')
                ->alter(fn (int $value) => 42)
                ->mapper()
                ->map(SimpleObject::class, ['value' => 'foo']);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('FOO!', $result->value);
    }

    public function test_value_not_accepted_by_value_altering_callback_is_not_used(): void
    {
        try {
            $result = $this->mapperBuilder
                ->alter(fn (string $value) => $value)
                ->mapper()
                ->map('string|null', null);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertNull($result);
    }

    public function test_alter_function_is_called_when_not_the_first_nor_the_last_one(): void
    {
        try {
            $result = $this->mapperBuilder
                ->alter(fn (int $value) => 404)
                ->alter(fn (string $value) => $value . '!')
                ->alter(fn (float $value) => 42.1337)
                ->mapper()
                ->map('string', 'some value');
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('some value!', $result);
    }
}
