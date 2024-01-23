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
        $filteredAttributes = $attributes->filter(fn (AttributeDefinition $attribute) => $attribute->class()->type()->className() === DateTimeImmutable::class);

        self::assertContainsEquals($attributeB, $filteredAttributes);
        self::assertNotContains($attributeA, $filteredAttributes);
        self::assertSame($attributeB, $filteredAttributes->toArray()[0]);
    }
}
