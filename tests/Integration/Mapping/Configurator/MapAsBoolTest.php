<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Configurator;

use CuyZ\Valinor\Mapper\Configurator\MapAsBool;
use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\TestWith;

final class MapAsBoolTest extends IntegrationTestCase
{
    #[TestWith(['input' => 1, 'expected' => true])]
    #[TestWith(['input' => '1', 'expected' => true])]
    #[TestWith(['input' => 'true', 'expected' => true])]
    #[TestWith(['input' => 0, 'expected' => false])]
    #[TestWith(['input' => '0', 'expected' => false])]
    #[TestWith(['input' => 'false', 'expected' => false])]
    public function test_attribute_maps_value_to_bool(string|int $input, bool $expected): void
    {
        $class = new class () {
            #[MapAsBool]
            public bool $value;
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

    public function test_attribute_with_custom_values_maps_value_to_bool(): void
    {
        $class = new class () {
            #[MapAsBool(true: ['enabled'], false: ['disabled'])]
            public bool $first;

            #[MapAsBool(true: ['enabled'], false: ['disabled'])]
            public bool $second;
        };

        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map($class::class, [
                    'first' => 'enabled',
                    'second' => 'disabled',
                ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertTrue($result->first);
        self::assertFalse($result->second);
    }

    public function test_attribute_maps_value_to_bool_on_promoted_property(): void
    {
        $class = new class (false) {
            public function __construct(
                #[MapAsBool]
                public bool $value,
            ) {}
        };

        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map($class::class, ['value' => 'true']);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertTrue($result->value);
    }

    public function test_attribute_maps_value_to_bool_on_union_target(): void
    {
        $class = new class () {
            #[MapAsBool]
            public bool|int $value;
        };

        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map($class::class, ['value' => 1]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertTrue($result->value);
    }

    public function test_attribute_passes_unconvertible_value_through_on_union_target(): void
    {
        $class = new class () {
            #[MapAsBool]
            public bool|int $value;
        };

        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map($class::class, ['value' => 5]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(5, $result->value);
    }

    public function test_attribute_passes_unconvertible_value_through_on_mixed_target(): void
    {
        $class = new class () {
            #[MapAsBool]
            public mixed $value;
        };

        try {
            $result = $this->mapperBuilder()
                ->allowPermissiveTypes()
                ->mapper()
                ->map($class::class, ['value' => 5]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(5, $result->value);
    }

    public function test_attribute_is_not_applied_when_union_target_has_no_bool_member(): void
    {
        $class = new class () {
            #[MapAsBool]
            public float|int $value;
        };

        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map($class::class, ['value' => 1]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(1, $result->value);
    }

    public function test_attribute_is_not_applied_when_target_cannot_hold_bool(): void
    {
        $class = new class () {
            #[MapAsBool]
            public int $value;
        };

        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map($class::class, ['value' => 1]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(1, $result->value);
    }

    public function test_unrecognized_value_raises_mapping_error(): void
    {
        $class = new class () {
            #[MapAsBool]
            public bool $value;
        };

        try {
            $this->mapperBuilder()
                ->mapper()
                ->map($class::class, ['value' => 'not a bool']);

            self::fail('Expected a mapping error to be raised.');
        } catch (MappingError $error) {
            self::assertSame('value', $error->messages()->toArray()[0]->path());
        }
    }
}
