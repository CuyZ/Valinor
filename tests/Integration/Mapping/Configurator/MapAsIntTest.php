<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Configurator;

use CuyZ\Valinor\Mapper\Configurator\MapAsInt;
use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\TestWith;

final class MapAsIntTest extends IntegrationTestCase
{
    #[TestWith(['input' => '42', 'expected' => 42])]
    #[TestWith(['input' => '0', 'expected' => 0])]
    #[TestWith(['input' => '-7', 'expected' => -7])]
    #[TestWith(['input' => '040', 'expected' => 40])]
    #[TestWith(['input' => '000', 'expected' => 0])]
    #[TestWith(['input' => '00040', 'expected' => 40])]
    public function test_attribute_maps_value_to_int(string $input, int $expected): void
    {
        $class = new class () {
            #[MapAsInt]
            public int $value;
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

    #[TestWith(['input' => 42.0, 'expected' => 42])]
    #[TestWith(['input' => 10.0, 'expected' => 10])]
    #[TestWith(['input' => -3.0, 'expected' => -3])]
    #[TestWith(['input' => 42, 'expected' => 42])]
    public function test_attribute_maps_integer_valued_number_to_int(int|float $input, int $expected): void
    {
        $class = new class () {
            #[MapAsInt]
            public int $value;
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

    public function test_attribute_maps_value_to_int_on_promoted_property(): void
    {
        $class = new class (0) {
            public function __construct(
                #[MapAsInt]
                public int $value,
            ) {}
        };

        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map($class::class, ['value' => '42']);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(42, $result->value);
    }

    public function test_attribute_maps_value_to_int_on_union_target(): void
    {
        $class = new class () {
            #[MapAsInt]
            public int|string $value;
        };

        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map($class::class, ['value' => '42']);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(42, $result->value);
    }

    public function test_attribute_passes_unconvertible_value_through_on_union_target(): void
    {
        $class = new class () {
            #[MapAsInt]
            public int|string $value;
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
            #[MapAsInt]
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

    public function test_attribute_is_not_applied_when_union_target_has_no_int_member(): void
    {
        $class = new class () {
            #[MapAsInt]
            public float|string $value;
        };

        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map($class::class, ['value' => '42']);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('42', $result->value);
    }

    public function test_non_integral_float_raises_mapping_error(): void
    {
        $class = new class () {
            #[MapAsInt]
            public int $value;
        };

        try {
            $this->mapperBuilder()
                ->mapper()
                ->map($class::class, ['value' => 42.5]);

            self::fail('Expected a mapping error to be raised.');
        } catch (MappingError $error) {
            self::assertSame('value', $error->messages()->toArray()[0]->path());
        }
    }

    public function test_unrecognized_value_raises_mapping_error(): void
    {
        $class = new class () {
            #[MapAsInt]
            public int $value;
        };

        try {
            $this->mapperBuilder()
                ->mapper()
                ->map($class::class, ['value' => 'not an int']);

            self::fail('Expected a mapping error to be raised.');
        } catch (MappingError $error) {
            // The unrecognized value is handed to the mapper untouched, so the
            // error message reports the original value and not `false` (which
            // would be the case if the raw value were not returned).
            self::assertMappingErrors($error, [
                'value' => "[invalid_integer] Value 'not an int' is not a valid integer.",
            ]);
        }
    }
}
