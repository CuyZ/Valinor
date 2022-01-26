<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Attribute;

use Attribute;
use CuyZ\Valinor\Attribute\StaticMethodConstructor;
use CuyZ\Valinor\Definition\ClassDefinition;
use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Object\Exception\TooManyObjectBuilderFactoryAttributes;
use CuyZ\Valinor\Mapper\Object\Factory\ObjectBuilderFactory;
use CuyZ\Valinor\Mapper\Object\MethodObjectBuilder;
use CuyZ\Valinor\Mapper\Object\ObjectBuilder;
use CuyZ\Valinor\Tests\Integration\IntegrationTest;

final class ObjectBuilderStrategyMappingTest extends IntegrationTest
{
    public function test_object_builder_attribute_is_used(): void
    {
        try {
            $result = $this->mapperBuilder->mapper()->map(ObjectWithBuilderStrategyAttribute::class, 'foo');
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $result->value);
        self::assertTrue($result->staticConstructorCalled);
    }

    public function test_repeated_object_builder_factory_attributes_throws_exception(): void
    {
        $factoryClass = ObjectBuilderFactory::class;
        $objectClass = ObjectWithSeveralBuilderStrategyAttributes::class;

        $this->expectException(TooManyObjectBuilderFactoryAttributes::class);
        $this->expectExceptionCode(1634044714);
        $this->expectExceptionMessage("Only one attribute of type `$factoryClass` is allowed, class `$objectClass` contains 2.");

        $this->mapperBuilder->mapper()->map($objectClass, 'foo');
    }
}

/**
 * @Annotation
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class ForeignAttribute
{
}

/**
 * @Annotation
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final class ObjectBuilderStrategyAttribute implements ObjectBuilderFactory
{
    public function for(ClassDefinition $class, $source): ObjectBuilder
    {
        return new MethodObjectBuilder($class, 'create');
    }
}

/**
 * @ForeignAttribute
 * @StaticMethodConstructor("create")
 */
#[ForeignAttribute]
#[StaticMethodConstructor('create')]
final class ObjectWithBuilderStrategyAttribute
{
    public string $value;

    public bool $staticConstructorCalled = false;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function create(string $value): self
    {
        $instance = new self($value);
        $instance->staticConstructorCalled = true;

        return $instance;
    }
}

/**
 * @ObjectBuilderStrategyAttribute
 * @ObjectBuilderStrategyAttribute
 */
#[ObjectBuilderStrategyAttribute]
#[ObjectBuilderStrategyAttribute]
final class ObjectWithSeveralBuilderStrategyAttributes
{
}
