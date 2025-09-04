<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Functional\Definition\Repository\Reflection;

use CuyZ\Valinor\Definition\Repository\Reflection\ReflectionAttributesRepository;
use CuyZ\Valinor\Definition\Repository\Reflection\ReflectionClassDefinitionRepository;
use CuyZ\Valinor\Tests\Fixture\Attribute\AttributeWithArguments;
use CuyZ\Valinor\Tests\Fixture\Attribute\AttributeWithClosure;
use CuyZ\Valinor\Tests\Fixture\Attribute\AttributeWithObject;
use CuyZ\Valinor\Tests\Fixture\Attribute\BasicAttribute;
use CuyZ\Valinor\Tests\Fixture\Attribute\PropertyTargetAttribute;
use CuyZ\Valinor\Type\Parser\Factory\TypeParserFactory;
use PHPUnit\Framework\Attributes\RequiresPhp;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use stdClass;

final class ReflectionAttributesRepositoryTest extends TestCase
{
    public function test_only_allowed_attributes_are_selected(): void
    {
        $class =
            new #[BasicAttribute(), AttributeWithArguments('foo', 42)] class () {};

        $reflection = new ReflectionClass($class::class);

        $attributes = $this->attributesRepository(
            allowedAttributes: [AttributeWithArguments::class]
        )->for($reflection);

        self::assertCount(1, $attributes);
        self::assertSame(AttributeWithArguments::class, $attributes[0]->class->name);
    }

    public function test_attributes_for_promoted_parameter_with_property_target_attribute_is_not_selected(): void
    {
        $class = new class (true) {
            public function __construct(
                #[PropertyTargetAttribute] public bool $promotedProperty,
            ) {}
        };

        $reflection = new ReflectionParameter([$class::class, '__construct'], 'promotedProperty');

        $attributes = $this->attributesRepository(
            allowedAttributes: [PropertyTargetAttribute::class]
        )->for($reflection);

        self::assertSame([], $attributes);
    }

    public function test_attribute_for_method_can_be_instantiated_when_containing_object(): void
    {
        $class = new class () {
            #[AttributeWithObject(new stdClass())]
            public function someMethod(): void {}
        };

        $reflection = (new ReflectionMethod($class::class, 'someMethod'));

        $attributes = $this->attributesRepository(allowedAttributes: [AttributeWithObject::class])->for($reflection);

        self::assertNull($attributes[0]->arguments);
        self::assertInstanceOf(AttributeWithObject::class, $attributes[0]->instantiate());
    }

    public function test_attribute_with_scalar_arguments_does_provide_arguments_to_attribute_definition(): void
    {
        $class = new #[AttributeWithArguments('foo', 42, ['baz' => 'fiz'])] class () {};

        $reflection = new ReflectionClass($class::class);

        $attributes = $this->attributesRepository(allowedAttributes: [AttributeWithArguments::class])->for($reflection);

        self::assertSame(['foo', 42, ['baz' => 'fiz']], $attributes[0]->arguments);
    }

    #[RequiresPhp('>=8.5')]
    public function test_attribute_with_closure_does_not_provide_arguments_to_attribute_definition(): void
    {
        /** @var object $class */
        $class = require 'ClassWithAttributeWithClosure.php';

        $reflection = new ReflectionClass($class::class);

        $attributes = $this->attributesRepository(allowedAttributes: [AttributeWithClosure::class])->for($reflection);

        self::assertNull($attributes[0]->arguments);
    }

    public function test_attribute_for_callable_is_immutable(): void
    {
        $class = new #[AttributeWithArguments('foo', 42, ['baz' => 'fiz'])] class () {};

        $reflection = new ReflectionClass($class::class);

        $attributes = $this->attributesRepository(allowedAttributes: [AttributeWithArguments::class])->for($reflection);

        $attribute = $attributes[0];
        $attributeForCallable = $attribute->forCallable(fn () => 'foo');

        self::assertNotSame($attribute, $attributeForCallable);
    }

    /**
     * @param list<class-string> $allowedAttributes
     */
    private function attributesRepository(array $allowedAttributes = []): ReflectionAttributesRepository
    {
        return new ReflectionAttributesRepository(
            new ReflectionClassDefinitionRepository(new TypeParserFactory(), $allowedAttributes),
            $allowedAttributes,
        );
    }
}
