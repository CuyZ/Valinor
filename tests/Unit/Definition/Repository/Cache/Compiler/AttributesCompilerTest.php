<?php

namespace CuyZ\Valinor\Tests\Unit\Definition\Repository\Cache\Compiler;

use CuyZ\Valinor\Definition\AttributeDefinition;
use CuyZ\Valinor\Definition\Attributes;
use CuyZ\Valinor\Definition\Repository\Cache\Compiler\AttributesCompiler;
use CuyZ\Valinor\Definition\Repository\Cache\Compiler\ClassDefinitionCompiler;
use CuyZ\Valinor\Tests\Fake\Definition\FakeClassDefinition;
use CuyZ\Valinor\Tests\Fixture\Attribute\AttributeWithArguments;
use CuyZ\Valinor\Tests\Fixture\Attribute\AttributeWithClosure;
use CuyZ\Valinor\Tests\Fixture\Attribute\BasicAttribute;
use CuyZ\Valinor\Tests\Unit\UnitTestCase;
use Error;
use PHPUnit\Framework\Attributes\RequiresPhp;
use stdClass;

final class AttributesCompilerTest extends UnitTestCase
{
    public function test_compiles_an_empty_attributes_instance_when_no_attributes_are_given(): void
    {
        $attributes = $this->compile(Attributes::empty());

        self::assertSame(Attributes::empty(), $attributes);
    }

    public function test_compiles_attributes_with_arguments(): void
    {
        $attributes = new Attributes(
            new AttributeDefinition(
                class: FakeClassDefinition::new(BasicAttribute::class),
                arguments: [],
                reflectionParts: [
                    'class',
                    stdClass::class,
                ],
                attributeIndex: 2,
            ),
            new AttributeDefinition(
                class: FakeClassDefinition::new(AttributeWithArguments::class),
                arguments: [
                    'foo',
                    42,
                    ['baz' => 'fiz'],
                ],
                reflectionParts: [
                    'property',
                    stdClass::class,
                    'someProperty',
                ],
                attributeIndex: 3,
            ),
        );

        $attributes = $this->compile($attributes)->toArray();

        self::assertCount(2, $attributes);

        $basicAttribute = $attributes[0]->instantiate();
        $attributeWithArguments = $attributes[1]->instantiate();

        self::assertInstanceOf(BasicAttribute::class, $basicAttribute);

        self::assertInstanceOf(AttributeWithArguments::class, $attributeWithArguments);

        self::assertSame('foo', $attributeWithArguments->foo);
        self::assertSame(42, $attributeWithArguments->bar);
        self::assertSame(['baz' => 'fiz'], $attributeWithArguments->array);
    }

    public function test_compiles_class_attributes_using_reflection(): void
    {
        $class = new
        #[BasicAttribute]
        #[AttributeWithArguments('foo', 42, ['foo' => 'foo'])]
        class () {};

        $attributes = new Attributes(
            new AttributeDefinition(
                class: FakeClassDefinition::new(BasicAttribute::class),
                arguments: null,
                reflectionParts: [
                    'class',
                    $class::class,
                ],
                attributeIndex: 0,
            ),
            new AttributeDefinition(
                class: FakeClassDefinition::new(AttributeWithArguments::class),
                arguments: null,
                reflectionParts: [
                    'class',
                    $class::class,
                ],
                attributeIndex: 1,
            ),
        );

        $attributes = $this->compile($attributes)->toArray();

        self::assertCount(2, $attributes);

        $basicAttribute = $attributes[0]->instantiate();
        $attributeWithArguments = $attributes[1]->instantiate();

        self::assertInstanceOf(BasicAttribute::class, $basicAttribute);

        self::assertInstanceOf(AttributeWithArguments::class, $attributeWithArguments);

        self::assertSame('foo', $attributeWithArguments->foo);
        self::assertSame(42, $attributeWithArguments->bar);
        self::assertSame(['foo' => 'foo'], $attributeWithArguments->array);
    }

    public function test_compiles_property_attributes_using_reflection(): void
    {
        $class = new class () {
            #[BasicAttribute]
            #[AttributeWithArguments('bar', 404, ['bar' => 'bar'])]
            public string $someProperty;
        };

        $attributes = new Attributes(
            new AttributeDefinition(
                class: FakeClassDefinition::new(BasicAttribute::class),
                arguments: null,
                reflectionParts: [
                    'property',
                    $class::class,
                    'someProperty',
                ],
                attributeIndex: 0,
            ),
            new AttributeDefinition(
                class: FakeClassDefinition::new(AttributeWithArguments::class),
                arguments: null,
                reflectionParts: [
                    'property',
                    $class::class,
                    'someProperty',
                ],
                attributeIndex: 1,
            ),
        );

        $attributes = $this->compile($attributes)->toArray();

        self::assertCount(2, $attributes);

        $basicAttribute = $attributes[0]->instantiate();
        $attributeWithArguments = $attributes[1]->instantiate();

        self::assertInstanceOf(BasicAttribute::class, $basicAttribute);

        self::assertInstanceOf(AttributeWithArguments::class, $attributeWithArguments);

        self::assertSame('bar', $attributeWithArguments->foo);
        self::assertSame(404, $attributeWithArguments->bar);
        self::assertSame(['bar' => 'bar'], $attributeWithArguments->array);
    }

    public function test_compiles_method_attributes_using_reflection(): void
    {
        $class = new class () {
            #[BasicAttribute]
            #[AttributeWithArguments('fiz', 1337, ['fiz' => 'fiz'])]
            public function someMethod(): void {}
        };

        $attributes = new Attributes(
            new AttributeDefinition(
                class: FakeClassDefinition::new(BasicAttribute::class),
                arguments: null,
                reflectionParts: [
                    'method',
                    $class::class,
                    'someMethod',
                ],
                attributeIndex: 0,
            ),
            new AttributeDefinition(
                class: FakeClassDefinition::new(AttributeWithArguments::class),
                arguments: null,
                reflectionParts: [
                    'method',
                    $class::class,
                    'someMethod',
                ],
                attributeIndex: 1,
            ),
        );

        $attributes = $this->compile($attributes)->toArray();

        self::assertCount(2, $attributes);

        $basicAttribute = $attributes[0]->instantiate();
        $attributeWithArguments = $attributes[1]->instantiate();

        self::assertInstanceOf(BasicAttribute::class, $basicAttribute);
        self::assertInstanceOf(AttributeWithArguments::class, $attributeWithArguments);

        self::assertSame('fiz', $attributeWithArguments->foo);
        self::assertSame(1337, $attributeWithArguments->bar);
        self::assertSame(['fiz' => 'fiz'], $attributeWithArguments->array);
    }

    public function test_compiles_method_parameters_attributes_using_reflection(): void
    {
        $class = new class () {
            public function someMethod(
                #[BasicAttribute]
                #[AttributeWithArguments('fiz', 1337, ['fiz' => 'fiz'])]
                string $someParameter,
            ): void {}
        };

        $attributes = new Attributes(
            new AttributeDefinition(
                class: FakeClassDefinition::new(BasicAttribute::class),
                arguments: null,
                reflectionParts: [
                    'methodParameter',
                    $class::class,
                    'someMethod',
                    0,
                ],
                attributeIndex: 0,
            ),
            new AttributeDefinition(
                class: FakeClassDefinition::new(AttributeWithArguments::class),
                arguments: null,
                reflectionParts: [
                    'methodParameter',
                    $class::class,
                    'someMethod',
                    0,
                ],
                attributeIndex: 1,
            ),
        );

        $attributes = $this->compile($attributes)->toArray();

        self::assertCount(2, $attributes);

        $basicAttribute = $attributes[0]->instantiate();
        $attributeWithArguments = $attributes[1]->instantiate();

        self::assertInstanceOf(BasicAttribute::class, $basicAttribute);
        self::assertInstanceOf(AttributeWithArguments::class, $attributeWithArguments);

        self::assertSame('fiz', $attributeWithArguments->foo);
        self::assertSame(1337, $attributeWithArguments->bar);
        self::assertSame(['fiz' => 'fiz'], $attributeWithArguments->array);
    }

    public function test_compiles_closure_attributes_using_reflection(): void
    {
        $closure =
            #[BasicAttribute]
            #[AttributeWithArguments('fiz', 1337, ['fiz' => 'fiz'])]
            fn () => 42;

        $attributes = (new Attributes(
            new AttributeDefinition(
                class: FakeClassDefinition::new(BasicAttribute::class),
                arguments: null,
                reflectionParts: ['closure'],
                attributeIndex: 0,
            ),
            new AttributeDefinition(
                class: FakeClassDefinition::new(AttributeWithArguments::class),
                arguments: null,
                reflectionParts: ['closure'],
                attributeIndex: 1,
            ),
        ))->forCallable($closure);

        $attributes = $this->compile($attributes)->forCallable($closure)->toArray();

        self::assertCount(2, $attributes);

        $basicAttribute = $attributes[0]->instantiate();
        $attributeWithArguments = $attributes[1]->instantiate();

        self::assertInstanceOf(BasicAttribute::class, $basicAttribute);
        self::assertInstanceOf(AttributeWithArguments::class, $attributeWithArguments);

        self::assertSame('fiz', $attributeWithArguments->foo);
        self::assertSame(1337, $attributeWithArguments->bar);
        self::assertSame(['fiz' => 'fiz'], $attributeWithArguments->array);
    }

    public function test_compiles_closure_parameter_attributes_using_reflection(): void
    {
        $closure =
            fn (
                #[BasicAttribute]
                #[AttributeWithArguments('fiz', 1337, ['fiz' => 'fiz'])]
                string $foo,
            ) => 42;

        $attributes = (new Attributes(
            new AttributeDefinition(
                class: FakeClassDefinition::new(BasicAttribute::class),
                arguments: null,
                reflectionParts: [
                    'closureParameter',
                    0,
                ],
                attributeIndex: 0,
            ),
            new AttributeDefinition(
                class: FakeClassDefinition::new(AttributeWithArguments::class),
                arguments: null,
                reflectionParts: [
                    'closureParameter',
                    0,
                ],
                attributeIndex: 1,
            ),
        ))->forCallable($closure);

        $attributes = $this->compile($attributes)->forCallable($closure)->toArray();

        self::assertCount(2, $attributes);

        $basicAttribute = $attributes[0]->instantiate();
        $attributeWithArguments = $attributes[1]->instantiate();

        self::assertInstanceOf(BasicAttribute::class, $basicAttribute);
        self::assertInstanceOf(AttributeWithArguments::class, $attributeWithArguments);

        self::assertSame('fiz', $attributeWithArguments->foo);
        self::assertSame(1337, $attributeWithArguments->bar);
        self::assertSame(['fiz' => 'fiz'], $attributeWithArguments->array);
    }

    #[RequiresPhp('>=8.5')]
    public function test_compiles_attribute_with_closure(): void
    {
        $attributes = new Attributes(
            new AttributeDefinition(
                class: FakeClassDefinition::new(AttributeWithClosure::class),
                arguments: null,
                reflectionParts: [
                    'class',
                    ClassWithAttributeWithClosure::class,
                ],
                attributeIndex: 0,
            ),
        );

        $attributes = $this->compile($attributes)->toArray();

        self::assertCount(1, $attributes);

        $attributeWithClosure = $attributes[0]->instantiate();

        self::assertInstanceOf(AttributeWithClosure::class, $attributeWithClosure);

        self::assertSame('foo', ($attributeWithClosure->closure)('FOO'));
    }

    private function compile(Attributes $attributes): Attributes
    {
        $attributesCompiler = new AttributesCompiler(new ClassDefinitionCompiler());

        $code = $attributesCompiler->compile($attributes);

        try {
            /** @var Attributes */
            return eval('return ' . $code . ';');
        } catch (Error $exception) {
            self::fail($exception->getMessage());
        }
    }
}
