<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Configurator;

use CuyZ\Valinor\Mapper\Configurator\MapAsString;
use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Fixture\Object\StringableObject;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\TestWith;

final class MapAsStringTest extends IntegrationTestCase
{
    #[TestWith(['input' => 42, 'expected' => '42'])]
    #[TestWith(['input' => 0, 'expected' => '0'])]
    #[TestWith(['input' => 4.5, 'expected' => '4.5'])]
    public function test_attribute_maps_value_to_string(int|float $input, string $expected): void
    {
        $class = new class () {
            #[MapAsString]
            public string $value;
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

    public function test_attribute_maps_value_to_string_on_promoted_property(): void
    {
        $class = new class ('') {
            public function __construct(
                #[MapAsString]
                public string $value,
            ) {}
        };

        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map($class::class, ['value' => 42]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('42', $result->value);
    }

    public function test_attribute_maps_value_to_string_on_union_target(): void
    {
        $class = new class () {
            #[MapAsString]
            public string|bool $value;
        };

        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map($class::class, ['value' => 42]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('42', $result->value);
    }

    public function test_attribute_passes_unconvertible_value_through_on_union_target(): void
    {
        $class = new class () {
            #[MapAsString]
            public string|bool $value;
        };

        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map($class::class, ['value' => true]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertTrue($result->value);
    }

    public function test_attribute_passes_unconvertible_value_through_on_mixed_target(): void
    {
        $class = new class () {
            #[MapAsString]
            public mixed $value;
        };

        try {
            $result = $this->mapperBuilder()
                ->allowPermissiveTypes()
                ->mapper()
                ->map($class::class, ['value' => true]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertTrue($result->value);
    }

    public function test_attribute_is_not_applied_when_union_target_has_no_string_member(): void
    {
        $class = new class () {
            #[MapAsString]
            public int|float $value;
        };

        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map($class::class, ['value' => 42]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(42, $result->value);
    }

    public function test_attribute_maps_stringable_object_to_string(): void
    {
        $class = new class () {
            #[MapAsString]
            public string $value;
        };

        try {
            $result = $this->mapperBuilder()
                ->mapper()
                ->map($class::class, ['value' => new StringableObject('foo')]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $result->value);
    }
}
