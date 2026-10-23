<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Configurator;

use CuyZ\Valinor\Mapper\Configurator\MapAsFloat;
use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\TestWith;

final class MapAsFloatTest extends IntegrationTestCase
{
    #[TestWith(['input' => '4.50', 'expected' => 4.5])]
    #[TestWith(['input' => '42', 'expected' => 42.0])]
    #[TestWith(['input' => '-1.5', 'expected' => -1.5])]
    public function test_attribute_maps_value_to_float(string $input, float $expected): void
    {
        $class = new class () {
            #[MapAsFloat]
            public float $value;
        };

        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map($class::class, ['value' => $input]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame($expected, $result->value);
    }

    public function test_attribute_maps_value_to_float_on_promoted_property(): void
    {
        $class = new class (0.0) {
            public function __construct(
                #[MapAsFloat]
                public float $value,
            ) {}
        };

        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map($class::class, ['value' => '4.50']);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(4.5, $result->value);
    }

    public function test_attribute_maps_value_to_float_on_union_target(): void
    {
        $class = new class () {
            #[MapAsFloat]
            public float|string $value;
        };

        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map($class::class, ['value' => '4.50']);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(4.5, $result->value);
    }

    public function test_attribute_passes_unconvertible_value_through_on_union_target(): void
    {
        $class = new class () {
            #[MapAsFloat]
            public float|string $value;
        };

        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map($class::class, ['value' => 'hello']);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('hello', $result->value);
    }

    public function test_attribute_passes_unconvertible_value_through_on_mixed_target(): void
    {
        $class = new class () {
            #[MapAsFloat]
            public mixed $value;
        };

        try {
            $result = $this->mapperBuilder()
                ->allowPermissiveTypes()
                ->mapper()
                ->map($class::class, ['value' => 'hello']);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('hello', $result->value);
    }

    public function test_attribute_is_not_applied_when_union_target_has_no_float_member(): void
    {
        $class = new class () {
            #[MapAsFloat]
            public int|string $value;
        };

        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map($class::class, ['value' => '1.5']);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('1.5', $result->value);
    }

    public function test_unrecognized_value_raises_mapping_error(): void
    {
        $class = new class () {
            #[MapAsFloat]
            public float $value;
        };

        try {
            $this->mapperBuilder()
                ->mapper()
                ->map($class::class, ['value' => 'not a float']);

            self::fail('Expected a mapping error to be raised.');
        } catch (MappingError $error) {
            // The unrecognized value is handed to the mapper untouched, so the
            // error message reports the original value and not `false` (which
            // would be the case if the raw value were not returned).
            self::assertMappingErrors($error, [
                'value' => "[invalid_float] Value 'not a float' is not a valid float.",
            ]);
        }
    }
}
