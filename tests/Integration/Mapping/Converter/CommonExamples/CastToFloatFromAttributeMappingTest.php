<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Converter\CommonExamples;

use Attribute;
use CuyZ\Valinor\Mapper\AsConverter;
use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;

use function is_numeric;

final class CastToFloatFromAttributeMappingTest extends IntegrationTestCase
{
    public function test_can_use_cast_to_float_converter_attribute(): void
    {
        $class = new class () {
            #[CastToFloat]
            public float $value;
        };

        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map($class::class, '1337.42');

            self::assertSame(1337.42, $result->value);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }
    }
}

#[Attribute, AsConverter]
final class CastToFloat
{
    /**
     * @param callable(string): float $next
     */
    public function map(string $value, callable $next): float
    {
        if (is_numeric($value)) {
            return (float)$value;
        }

        return $next($value);
    }
}
