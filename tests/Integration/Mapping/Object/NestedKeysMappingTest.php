<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Object;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;

final class NestedKeysMappingTest extends IntegrationTestCase
{
    public function test_nested_keys_mapping_with_name_collision(): void
    {
        $source = [
            'id' => '8027af91-85df-4a2e-a2d5-67c74cf3f21a',
            'taxonomy' => 'services',
        ];

        try {
            $result = $this->mapperBuilder()->mapper()->map(Order::class, $source);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('8027af91-85df-4a2e-a2d5-67c74cf3f21a', $result->id->id);
        self::assertSame('services', $result->id->taxonomy);
    }

    public function test_nested_keys_mapping_without_name_collision(): void
    {
        $source = [
            'id' => 'product-123',
            'type' => 'physical',
        ];

        try {
            $result = $this->mapperBuilder()->mapper()->map(Product::class, $source);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('product-123', $result->ref->id);
        self::assertSame('physical', $result->ref->type);
    }

    public function test_nested_keys_mapping_with_three_parameters(): void
    {
        $source = [
            'street' => '123 Main St',
            'city' => 'Springfield',
            'country' => 'USA',
        ];

        try {
            $result = $this->mapperBuilder()->mapper()->map(Person::class, $source);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('123 Main St', $result->address->street);
        self::assertSame('Springfield', $result->address->city);
        self::assertSame('USA', $result->address->country);
    }

    public function test_nested_keys_mapping_with_optional_parameters(): void
    {
        $source = [
            'name' => 'production',
        ];

        try {
            $result = $this->mapperBuilder()->mapper()->map(Settings::class, $source);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('production', $result->config->name);
        self::assertNull($result->config->description);
    }

    public function test_nested_keys_mapping_with_custom_constructor(): void
    {
        $source = [
            'value' => 'custom-value',
            'label' => 'Custom Label',
        ];

        try {
            $result = $this->mapperBuilder()->mapper()->map(Item::class, $source);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('custom-value', $result->ref->value);
        self::assertSame('Custom Label', $result->ref->label);
    }

    public function test_already_nested_data_still_works(): void
    {
        $source = [
            'id' => [
                'id' => 'nested-123',
                'taxonomy' => 'categories',
            ]
        ];

        try {
            $result = $this->mapperBuilder()->mapper()->map(Order::class, $source);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('nested-123', $result->id->id);
        self::assertSame('categories', $result->id->taxonomy);
    }

    public function test_multiple_properties_with_nested_objects(): void
    {
        $source = [
            'ref' => [
                'a' => 'value-a',
                'b' => 'value-b',
            ],
            'extra' => 'extra-value',
        ];

        try {
            $result = $this->mapperBuilder()->mapper()->map(Container::class, $source);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('value-a', $result->ref->a);
        self::assertSame('value-b', $result->ref->b);
        self::assertSame('extra-value', $result->extra);
    }

    public function test_three_level_nesting_with_nested_keys(): void
    {
        $flatSource = [
            'id' => '123',
            'name' => 'test',
        ];

        try {
            $result = $this->mapperBuilder()->mapper()->map(Level1::class, $flatSource);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('123', $result->nested->data->id);
        self::assertSame('test', $result->nested->data->name);
    }

    public function test_nested_object_with_parent_having_additional_properties(): void
    {
        $source = [
            'id' => '8027af91-85df-4a2e-a2d5-67c74cf3f21a',
            'taxonomy' => 'services',
            'title' => 'Test Item',
            'description' => 'A test item with nested ref',
            'active' => true,
        ];

        try {
            $result = $this->mapperBuilder()->mapper()->map(CatalogItem::class, $source);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('8027af91-85df-4a2e-a2d5-67c74cf3f21a', $result->id->id);
        self::assertSame('services', $result->id->taxonomy);
        self::assertSame('Test Item', $result->title);
        self::assertSame('A test item with nested ref', $result->description);
        self::assertTrue($result->active);
    }

    public function test_nested_object_with_optional_param_and_parent_additional_properties(): void
    {
        $source = [
            'id' => '123',
            'extra' => 'data',
        ];

        try {
            $result = $this->mapperBuilder()->mapper()->map(EntityWithOptional::class, $source);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('123', $result->id->id);
        self::assertNull($result->id->type);
        self::assertSame('data', $result->extra);
    }

    public function test_nested_keys_not_detected_when_no_params_provided(): void
    {
        // This test kills the LogicalAnd → LogicalOr mutation
        // An object with all optional params where none are provided should NOT trigger nested keys detection
        $source = [
            'data' => 'value',
        ];

        try {
            $result = $this->mapperBuilder()->mapper()->map(WrapperWithAllOptional::class, $source);
            self::fail('Should have thrown MappingError');
        } catch (MappingError $error) {
            // Expected: 'data' should be an object, not a string
            // If mutation escapes (|| instead of &&), it would try to map empty object
            self::assertStringContainsString('data', $error->getMessage());
        }
    }

    public function test_property_key_not_included_in_consumed_keys(): void
    {
        // This test kills the ArrayItemRemoval mutation on line 164
        // When mapping 'id' => {id: string, taxonomy: string},
        // consumed_keys should be ['taxonomy'] NOT ['id', 'taxonomy']
        $source = [
            'id' => 'uuid-123',
            'taxonomy' => 'products',
            'name' => 'Test',
        ];

        try {
            $result = $this->mapperBuilder()->mapper()->map(ItemWithName::class, $source);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        // If the mutation escapes, 'id' would be in consumed_keys,
        // causing 'id' to be missing from parent, leading to error
        self::assertSame('uuid-123', $result->id->id);
        self::assertSame('products', $result->id->taxonomy);
        self::assertSame('Test', $result->name);
    }

    public function test_already_array_value_not_processed_as_nested_keys(): void
    {
        // This test kills the ReturnRemoval mutation on line 126
        // When value is already an array, it should return early
        // and NOT continue to nested keys detection logic
        $source = [
            'data' => [
                'x' => 'value-x',
                'y' => 'value-y',
                'z' => 'extra',  // This key doesn't exist in DataObject constructor
            ]
        ];

        try {
            $result = $this->mapperBuilder()
                ->allowSuperfluousKeys()
                ->mapper()
                ->map(WrapperWithData::class, $source);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('value-x', $result->data->x);
        self::assertSame('value-y', $result->data->y);
    }
}

final readonly class OrderId
{
    public function __construct(
        public string $id,
        public string $taxonomy
    ) {}
}

final class Order
{
    public function __construct(
        public OrderId $id
    ) {}
}

final class ProductRef
{
    public function __construct(
        public string $id,
        public string $type
    ) {}
}

final class Product
{
    public function __construct(
        public ProductRef $ref
    ) {}
}

final class Address
{
    public function __construct(
        public string $street,
        public string $city,
        public string $country
    ) {}
}

final class Person
{
    public function __construct(
        public Address $address
    ) {}
}

final class Config
{
    public function __construct(
        public string $name,
        public ?string $description = null
    ) {}
}

final class Settings
{
    public function __construct(
        public Config $config
    ) {}
}

final readonly class CustomRef
{
    public function __construct(
        public string $value,
        public string $label
    ) {}

    #[\CuyZ\Valinor\Mapper\Object\Constructor]
    public static function create(string $value, string $label): self
    {
        return new self($value, $label);
    }
}

final class Item
{
    public function __construct(
        public CustomRef $ref
    ) {}
}

final class SimpleRef
{
    public function __construct(
        public string $a,
        public string $b
    ) {}
}

final class Container
{
    public function __construct(
        public SimpleRef $ref,
        public string $extra
    ) {}
}

final class Level3
{
    public function __construct(
        public string $id,
        public string $name
    ) {}
}

final class Level2
{
    public function __construct(
        public Level3 $data
    ) {}
}

final class Level1
{
    public function __construct(
        public Level2 $nested
    ) {}
}

final class ItemRef
{
    public function __construct(
        public string $id,
        public string $taxonomy
    ) {}
}

final class CatalogItem
{
    public function __construct(
        public ItemRef $id,
        public string $title,
        public string $description,
        public bool $active
    ) {}
}

final class RefWithOptional
{
    public function __construct(
        public string $id,
        public ?string $type = null
    ) {}
}

final class EntityWithOptional
{
    public function __construct(
        public RefWithOptional $id,
        public string $extra
    ) {}
}

final class AllOptionalRef
{
    public function __construct(
        public ?string $a = null,
        public ?string $b = null
    ) {}
}

final class WrapperWithAllOptional
{
    public function __construct(
        public AllOptionalRef $data
    ) {}
}

final class RefWithId
{
    public function __construct(
        public string $id,
        public string $taxonomy
    ) {}
}

final class ItemWithName
{
    public function __construct(
        public RefWithId $id,
        public string $name
    ) {}
}

final class DataObject
{
    public function __construct(
        public string $x,
        public string $y
    ) {}
}

final class WrapperWithData
{
    public function __construct(
        public DataObject $data
    ) {}
}
