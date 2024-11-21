<?php

namespace CuyZ\Valinor\Tests\Functional\Definition\Repository\Cache\Compiler;

use CuyZ\Valinor\Definition\AttributeDefinition;
use CuyZ\Valinor\Definition\Attributes;
use CuyZ\Valinor\Definition\Repository\Cache\Compiler\AttributesCompiler;
use CuyZ\Valinor\Definition\Repository\Cache\Compiler\ClassDefinitionCompiler;
use CuyZ\Valinor\Tests\Fake\Definition\FakeAttributes;
use CuyZ\Valinor\Tests\Fixture\Attribute\AttributeWithArguments;
use CuyZ\Valinor\Tests\Fixture\Attribute\BasicAttribute;
use CuyZ\Valinor\Tests\Fixture\Attribute\NestedAttribute;
use CuyZ\Valinor\Tests\Fixture\Attribute\PropertyTargetAttribute;
use CuyZ\Valinor\Tests\Fixture\Object\ObjectWithAttributes;
use CuyZ\Valinor\Tests\Fixture\Object\ObjectWithNestedAttributes;
use Error;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

final class AttributesCompilerTest extends TestCase
{
    private AttributesCompiler $attributesCompiler;

    protected function setUp(): void
    {
        $this->attributesCompiler = new AttributesCompiler(new ClassDefinitionCompiler());
    }

    public function test_compiles_an_empty_attributes_instance_when_no_attributes_are_given(): void
    {
        $attributes = $this->compile(Attributes::empty());

        self::assertSame(Attributes::empty(), $attributes);
    }

    public function test_compiles_attributes_for_class_with_attributes(): void
    {
        $reflection = new ReflectionClass(ObjectWithAttributes::class);
        $attributes = FakeAttributes::fromReflection($reflection);

        $attributes = $this->compile($attributes);

        self::assertCount(2, $attributes);
        self::assertTrue($attributes->has(BasicAttribute::class));
        self::assertTrue($attributes->has(AttributeWithArguments::class));

        $attribute = $attributes->filter(
            fn (AttributeDefinition $attribute) => $attribute->class->type->className() === AttributeWithArguments::class
        )->toArray()[0]->instantiate();

        /** @var AttributeWithArguments $attribute */
        self::assertSame('foo', $attribute->foo);
        self::assertSame('bar', $attribute->bar);
        self::assertSame('bar', $attribute->object->__toString());
        self::assertSame(['baz' => 'fiz'], $attribute->array);
    }

    public function test_compiles_attributes_for_class_without_attributes(): void
    {
        $reflection = new ReflectionClass(new class () {});
        $attributes = FakeAttributes::fromReflection($reflection);

        $attributes = $this->compile($attributes);

        self::assertSame(Attributes::empty(), $attributes);
    }

    public function test_compiles_attributes_for_class_with_nested_attributes(): void
    {
        $reflection = new ReflectionClass(ObjectWithNestedAttributes::class);
        $attributes = FakeAttributes::fromReflection($reflection);

        $attributes = $this->compile($attributes);

        self::assertCount(3, $attributes);
        self::assertTrue($attributes->has(BasicAttribute::class));
        self::assertTrue($attributes->has(AttributeWithArguments::class));
        self::assertTrue($attributes->has(NestedAttribute::class));

        $attribute = $attributes->filter(
            fn (AttributeDefinition $attribute) => $attribute->class->type->className() === AttributeWithArguments::class
        )->toArray()[0]->instantiate();

        /** @var AttributeWithArguments $attribute */
        self::assertSame('foo', $attribute->foo);
        self::assertSame('bar', $attribute->bar);

        $attribute = $attributes->filter(
            fn (AttributeDefinition $attribute) => $attribute->class->type->className() === NestedAttribute::class
        )->toArray()[0]->instantiate();

        /** @var NestedAttribute $attribute */
        self::assertCount(2, $attribute->nestedAttributes);
        self::assertInstanceOf(BasicAttribute::class, $attribute->nestedAttributes[0]);
        self::assertInstanceOf(AttributeWithArguments::class, $attribute->nestedAttributes[1]);

        /** @var AttributeWithArguments $nestedAttribute */
        $nestedAttribute = $attribute->nestedAttributes[1];

        self::assertSame('foo', $nestedAttribute->foo);
        self::assertSame('bar', $nestedAttribute->bar);
    }

    public function test_compiles_attributes_for_property_with_nested_attributes(): void
    {
        $reflection = new ReflectionProperty(ObjectWithNestedAttributes::class, 'property');
        $attributes = FakeAttributes::fromReflection($reflection);

        $attributes = $this->compile($attributes);

        self::assertCount(3, $attributes);
        self::assertTrue($attributes->has(BasicAttribute::class));
        self::assertTrue($attributes->has(AttributeWithArguments::class));
        self::assertTrue($attributes->has(NestedAttribute::class));
    }

    public function test_compiles_attributes_for_method_with_nested_attributes(): void
    {
        $reflection = new ReflectionMethod(ObjectWithNestedAttributes::class, 'method');
        $attributes = FakeAttributes::fromReflection($reflection);

        $attributes = $this->compile($attributes);

        self::assertCount(3, $attributes);
        self::assertTrue($attributes->has(BasicAttribute::class));
        self::assertTrue($attributes->has(AttributeWithArguments::class));
        self::assertTrue($attributes->has(NestedAttribute::class));
    }

    public function test_compiles_attributes_for_promoted_property_with_property_target_attribute(): void
    {
        $reflection = new ReflectionProperty(ObjectWithAttributes::class, 'promotedProperty');
        $attributes = FakeAttributes::fromReflection($reflection);

        $attributes = $this->compile($attributes);

        self::assertCount(1, $attributes);
        self::assertTrue($attributes->has(PropertyTargetAttribute::class));
    }

    private function compile(Attributes $attributes): Attributes
    {
        $code = $this->attributesCompiler->compile($attributes);

        try {
            return eval('return ' . $code . ';');
        } catch (Error $exception) {
            self::fail($exception->getMessage());
        }
    }
}
