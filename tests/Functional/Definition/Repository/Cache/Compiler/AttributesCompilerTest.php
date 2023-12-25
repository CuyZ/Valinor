<?php

namespace CuyZ\Valinor\Tests\Functional\Definition\Repository\Cache\Compiler;

use AssertionError;
use CuyZ\Valinor\Definition\Attributes;
use CuyZ\Valinor\Definition\AttributesContainer;
use CuyZ\Valinor\Definition\NativeAttributes;
use CuyZ\Valinor\Definition\Repository\Cache\Compiler\AttributesCompiler;
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
use ReflectionParameter;
use ReflectionProperty;

final class AttributesCompilerTest extends TestCase
{
    private AttributesCompiler $attributesCompiler;

    protected function setUp(): void
    {
        $this->attributesCompiler = new AttributesCompiler();
    }

    public function test_compiles_an_empty_attributes_instance_when_no_attributes_are_given(): void
    {
        $attributes = $this->compile(new FakeAttributes());

        self::assertSame(AttributesContainer::empty(), $attributes);
    }

    public function test_compiles_native_php_attributes_for_class_with_attributes(): void
    {
        $reflection = new ReflectionClass(ObjectWithAttributes::class);

        $attributes = $this->compile(new NativeAttributes($reflection));

        self::assertCount(2, $attributes);
        self::assertTrue($attributes->has(BasicAttribute::class));
        self::assertTrue($attributes->has(AttributeWithArguments::class));

        $attribute = [...$attributes->ofType(AttributeWithArguments::class)][0];

        self::assertSame('foo', $attribute->foo);
        self::assertSame('bar', $attribute->bar);
    }

    public function test_compiles_native_php_attributes_for_class_without_attributes(): void
    {
        $reflection = new ReflectionClass(new class () {});
        $attributes = $this->compile(new NativeAttributes($reflection));

        self::assertSame(AttributesContainer::empty(), $attributes);
    }

    /**
     * @requires PHP >= 8.1
     */
    public function test_compiles_native_php_attributes_for_class_with_nested_attributes(): void
    {
        $reflection = new ReflectionClass(ObjectWithNestedAttributes::class);

        $attributes = $this->compile(new NativeAttributes($reflection));

        self::assertCount(3, $attributes);
        self::assertTrue($attributes->has(BasicAttribute::class));
        self::assertTrue($attributes->has(AttributeWithArguments::class));
        self::assertTrue($attributes->has(NestedAttribute::class));

        /** @var AttributeWithArguments $attribute */
        $attribute = [...$attributes->ofType(AttributeWithArguments::class)][0];

        self::assertSame('foo', $attribute->foo);
        self::assertSame('bar', $attribute->bar);

        $attribute = [...$attributes->ofType(NestedAttribute::class)][0];

        self::assertCount(2, $attribute->nestedAttributes);
        self::assertInstanceOf(BasicAttribute::class, $attribute->nestedAttributes[0]);
        self::assertInstanceOf(AttributeWithArguments::class, $attribute->nestedAttributes[1]);

        /** @var AttributeWithArguments $nestedAttribute */
        $nestedAttribute = $attribute->nestedAttributes[1];

        self::assertSame('foo', $nestedAttribute->foo);
        self::assertSame('bar', $nestedAttribute->bar);
    }

    /**
     * @requires PHP >= 8.1
     */
    public function test_compiles_native_php_attributes_for_property_with_nested_attributes(): void
    {
        $reflection = new ReflectionClass(ObjectWithNestedAttributes::class);
        $reflection = $reflection->getProperty('property');

        $attributes = $this->compile(new NativeAttributes($reflection));

        self::assertCount(3, $attributes);
        self::assertTrue($attributes->has(BasicAttribute::class));
        self::assertTrue($attributes->has(AttributeWithArguments::class));
        self::assertTrue($attributes->has(NestedAttribute::class));
    }

    /**
     * @requires PHP >= 8.1
     */
    public function test_compiles_native_php_attributes_for_method_with_nested_attributes(): void
    {
        $reflection = new ReflectionClass(ObjectWithNestedAttributes::class);
        $reflection = $reflection->getMethod('method');

        $attributes = $this->compile(new NativeAttributes($reflection));

        self::assertCount(3, $attributes);
        self::assertTrue($attributes->has(BasicAttribute::class));
        self::assertTrue($attributes->has(AttributeWithArguments::class));
        self::assertTrue($attributes->has(NestedAttribute::class));
    }

    public function test_compiles_native_php_attributes_for_promoted_property_with_property_target_attribute(): void
    {
        $reflection = new ReflectionProperty(ObjectWithAttributes::class, 'promotedProperty');
        $attributes = $this->compile(new NativeAttributes($reflection));

        self::assertCount(1, $attributes);
        self::assertTrue($attributes->has(PropertyTargetAttribute::class));
    }

    public function test_compiles_an_empty_attributes_instance_for_promoted_parameter_with_property_target_attribute(): void
    {
        $reflection = new ReflectionParameter([ObjectWithAttributes::class, '__construct'], 'promotedProperty');
        $attributes = $this->compile(new NativeAttributes($reflection));

        self::assertSame(AttributesContainer::empty(), $attributes);
    }

    public function test_invalid_attributes_instance_throws_assertion_error(): void
    {
        $this->expectException(AssertionError::class);

        $this->compile(FakeAttributes::notEmpty());
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
