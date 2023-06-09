<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Definition;

use CuyZ\Valinor\Definition\NativeAttributes;
use CuyZ\Valinor\Tests\Fixture\Attribute\AttributeWithArguments;
use CuyZ\Valinor\Tests\Fixture\Attribute\BasicAttribute;
use CuyZ\Valinor\Tests\Fixture\Object\ObjectWithAttributes;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;

use function iterator_to_array;

final class NativeAttributesTest extends TestCase
{
    public function test_empty_attributes_returns_empty_results(): void
    {
        $object = new class () {
            public string $property;

            public function method(string $parameter): void {}
        };

        $reflections = [
            new ReflectionClass($object),
            new ReflectionProperty($object, 'property'),
            new ReflectionMethod($object, 'method'),
            new ReflectionParameter([$object, 'method'], 'parameter'),
        ];

        foreach ($reflections as $reflection) {
            $attributes = new NativeAttributes($reflection);

            self::assertEmpty(iterator_to_array($attributes));
            self::assertCount(0, $attributes);
            self::assertFalse($attributes->has(BasicAttribute::class));
            self::assertEmpty($attributes->ofType(BasicAttribute::class));
        }
    }

    public function test_class_attributes_are_fetched_correctly(): void
    {
        $reflection = new ReflectionClass(ObjectWithAttributes::class);
        $attributes = new NativeAttributes($reflection);

        self::assertCount(2, $attributes);
        self::assertTrue($attributes->has(BasicAttribute::class));
        self::assertTrue($attributes->has(AttributeWithArguments::class));
        self::assertCount(1, $attributes->ofType(BasicAttribute::class));
        self::assertCount(1, $attributes->ofType(AttributeWithArguments::class));
    }

    public function test_property_attributes_are_fetched_correctly(): void
    {
        $reflection = new ReflectionProperty(ObjectWithAttributes::class, 'property');
        $attributes = new NativeAttributes($reflection);

        self::assertCount(2, $attributes);
        self::assertTrue($attributes->has(BasicAttribute::class));
        self::assertTrue($attributes->has(AttributeWithArguments::class));
        self::assertCount(1, $attributes->ofType(BasicAttribute::class));
        self::assertCount(1, $attributes->ofType(AttributeWithArguments::class));
    }

    public function test_method_attributes_are_fetched_correctly(): void
    {
        $reflection = new ReflectionMethod(ObjectWithAttributes::class, 'method');
        $attributes = new NativeAttributes($reflection);

        self::assertCount(2, $attributes);
        self::assertTrue($attributes->has(BasicAttribute::class));
        self::assertTrue($attributes->has(AttributeWithArguments::class));
        self::assertCount(1, $attributes->ofType(BasicAttribute::class));
        self::assertCount(1, $attributes->ofType(AttributeWithArguments::class));
    }

    public function test_parameter_attributes_are_fetched_correctly(): void
    {
        $reflection = new ReflectionParameter([ObjectWithAttributes::class, 'method'], 'parameter');
        $attributes = new NativeAttributes($reflection);

        self::assertCount(1, $attributes);
        self::assertTrue($attributes->has(BasicAttribute::class));
        self::assertCount(1, $attributes->ofType(BasicAttribute::class));
    }

    public function test_function_attributes_are_fetched_correctly(): void
    {
        $reflection = new ReflectionFunction(
            #[BasicAttribute]
            fn () => 'foo'
        );
        $attributes = new NativeAttributes($reflection);

        self::assertCount(1, $attributes);
        self::assertTrue($attributes->has(BasicAttribute::class));
        self::assertCount(1, $attributes->ofType(BasicAttribute::class));
    }
}
