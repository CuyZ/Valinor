<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Converter\CommonExamples;

use Attribute;
use CuyZ\Valinor\Mapper\AsConverter;
use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;

final class CastToBoolFromAttributeMappingTest extends IntegrationTestCase
{
    public function test_can_use_cast_to_bool_converter_attribute(): void
    {
        $class = new class () {
            public string $name;

            #[CastToBool]
            public bool $isActive;
        };

        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map($class::class, [
                    'name' => 'John Doe',
                    'isActive' => 'yes',
                ]);

            self::assertSame('John Doe', $result->name);
            self::assertSame(true, $result->isActive);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }
    }
}

#[Attribute, AsConverter]
final class CastToBool
{
    /**
     * @param callable(mixed): bool $next
     */
    public function map(string $value, callable $next): bool
    {
        $value = match ($value) {
            'yes', 'on' => true,
            'no', 'off' => false,
            default => $value,
        };

        return $next($value);
    }
}
