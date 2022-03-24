<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Attribute;

use Attribute;
use CuyZ\Valinor\Attribute\StaticMethodConstructor;
use CuyZ\Valinor\Definition\ClassDefinition;
use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Object\Exception\TooManyObjectBuilderFactoryAttributes;
use CuyZ\Valinor\Mapper\Object\Factory\ObjectBuilderFactory;
use CuyZ\Valinor\Mapper\Object\ObjectBuilder;
use CuyZ\Valinor\Tests\Fake\Mapper\Object\FakeObjectBuilder;
use CuyZ\Valinor\Tests\Integration\IntegrationTest;
use RuntimeException;

final class ObjectBuilderStrategyMappingTest extends IntegrationTest
{
    public function test_object_builder_attribute_is_used(): void
    {
        try {
            $result = $this->mapperBuilder->mapper()->map(ObjectWithBuilderStrategyAttribute::class, [
                'foo' => 'foo',
                'bar' => 'bar',
            ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $result->foo);
        self::assertSame('bar', $result->bar);
        self::assertTrue($result->staticConstructorCalled);
    }

    public function test_named_constructor_throwing_exception_is_caught_by_mapper(): void
    {
        try {
            $this->mapperBuilder->mapper()->map(ObjectWithFailingBuilderStrategyAttribute::class, []);
        } catch (MappingError $exception) {
            $error = $exception->node()->messages()[0];

            self::assertSame('some exception', (string)$error);
        }
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
        return new FakeObjectBuilder();
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
    public bool $staticConstructorCalled = false;

    public string $foo;

    public string $bar;

    private function __construct(string $foo, string $bar)
    {
        $this->foo = $foo;
        $this->bar = $bar;
    }

    public static function create(string $foo, string $bar = 'optional value'): self
    {
        $instance = new self($foo, $bar);
        $instance->staticConstructorCalled = true;

        return $instance;
    }
}

/**
 * @StaticMethodConstructor("failingConstructor")
 */
#[StaticMethodConstructor('failingConstructor')]
final class ObjectWithFailingBuilderStrategyAttribute
{
    public static function failingConstructor(): self
    {
        throw new RuntimeException('some exception');
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
