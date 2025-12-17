<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Definition;

use CuyZ\Valinor\Definition\AttributeDefinition;
use CuyZ\Valinor\Definition\Attributes;
use CuyZ\Valinor\Tests\Fake\Definition\FakeAttributeDefinition;
use CuyZ\Valinor\Tests\Fixture\Attribute\BasicAttribute;
use DateTimeImmutable;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;
use stdClass;

use function iterator_to_array;

final class AttributesTest extends TestCase
{
    public function test_empty_attributes_is_empty_and_remains_the_same_instance(): void
    {
        $attributes = Attributes::empty();

        self::assertSame($attributes, Attributes::empty());
        self::assertCount(0, $attributes);
        self::assertFalse($attributes->has(BasicAttribute::class));
        self::assertSame([], $attributes->toArray());
    }

    public function test_attributes_are_countable(): void
    {
        $container = new Attributes(
            FakeAttributeDefinition::new(),
            FakeAttributeDefinition::new(),
            FakeAttributeDefinition::new(),
        );

        self::assertCount(3, $container);
    }

    public function test_attributes_are_traversable(): void
    {
        $attributes = [
            FakeAttributeDefinition::new(),
            FakeAttributeDefinition::new(),
            FakeAttributeDefinition::new(),
        ];

        $container = new Attributes(...$attributes);

        self::assertSame($attributes, iterator_to_array($container));
        self::assertSame($attributes, $container->toArray());
    }

    public function test_attributes_has_type_checks_all_attributes(): void
    {
        $attributes = new Attributes(FakeAttributeDefinition::new(DateTimeImmutable::class));

        self::assertTrue($attributes->has(DateTimeInterface::class));
        self::assertFalse($attributes->has(stdClass::class));
    }

    public function test_attributes_of_type_filters_on_given_class_name(): void
    {
        $attributeA = FakeAttributeDefinition::new();
        $attributeB = FakeAttributeDefinition::new(DateTimeImmutable::class);

        $attributes = new Attributes($attributeA, $attributeB);
        $filteredAttributes = $attributes->filter(fn (AttributeDefinition $attribute) => $attribute->class->type->className() === DateTimeImmutable::class);

        self::assertCount(1, $filteredAttributes);
        self::assertSame($attributeB, $filteredAttributes->toArray()[0]);
    }

    public function test_first_of_returns_correct_attribute(): void
    {
        $attributeA = FakeAttributeDefinition::new(self::class);
        $attributeB = FakeAttributeDefinition::new(DateTimeImmutable::class);

        $attributes = new Attributes($attributeA, $attributeB);
        $firstAttribute = $attributes->firstOf(DateTimeImmutable::class);

        self::assertSame($attributeB, $firstAttribute);
    }

    public function test_merge_attributes_merges_attributes(): void
    {
        $attributeA = FakeAttributeDefinition::new();
        $attributeB = FakeAttributeDefinition::new();
        $attributeC = FakeAttributeDefinition::new();

        $attributesA = new Attributes($attributeA, $attributeB);
        $attributesB = new Attributes($attributeC);

        $mergedAttributes = $attributesA->merge($attributesB);

        self::assertCount(3, $mergedAttributes);
        self::assertSame([$attributeA, $attributeB, $attributeC], $mergedAttributes->toArray());

        self::assertNotSame($attributesA, $mergedAttributes);
    }
}
