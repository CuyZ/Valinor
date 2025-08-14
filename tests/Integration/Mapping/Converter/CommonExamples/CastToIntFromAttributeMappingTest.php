<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Converter\CommonExamples;

use Attribute;
use CuyZ\Valinor\Mapper\AsConverter;
use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;

final class CastToIntFromAttributeMappingTest extends IntegrationTestCase
{
    public function test_can_use_cast_to_int_converter_attribute(): void
    {
        $class = new class () {
            #[CastToInt]
            public int $value;
        };

        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map($class::class, '42');

            self::assertSame(42, $result->value);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }
    }
}

#[Attribute, AsConverter]
final class CastToInt
{
    /**
     * @param callable(string): int $next
     */
    public function map(string $value, callable $next): int
    {
        if (filter_var($value, FILTER_VALIDATE_INT)) {
            return (int)$value;
        }

        return $next($value);
    }
}
