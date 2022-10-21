<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Attribute;

use Attribute;
use CuyZ\Valinor\Attribute\StaticMethodConstructor;
use CuyZ\Valinor\Definition\ClassDefinition;
use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Object\Exception\TooManyObjectBuilderFactoryAttributes;
use CuyZ\Valinor\Mapper\Object\Factory\ObjectBuilderFactory;
use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Tests\Fake\Mapper\Object\FakeObjectBuilder;
use CuyZ\Valinor\Tests\Fake\Mapper\Tree\Message\FakeErrorMessage;
use CuyZ\Valinor\Tests\Integration\IntegrationTest;

final class ObjectBuilderStrategyMappingTest extends IntegrationTest
{
    public function test_object_builder_attribute_is_used(): void
    {
        try {
            $result = (new MapperBuilder())->mapper()->map(ObjectWithBuilderStrategyAttribute::class, [
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
            (new MapperBuilder())->mapper()->map(ObjectWithFailingBuilderStrategyAttribute::class, []);
        } catch (MappingError $exception) {
            $error = $exception->node()->messages()[0];

            self::assertSame('1656076067', $error->code());
            self::assertSame('some error message', (string)$error);
        }
    }

    public function test_repeated_object_builder_factory_attributes_throws_exception(): void
    {
        $factoryClass = ObjectBuilderFactory::class;
        $objectClass = ObjectWithSeveralBuilderStrategyAttributes::class;

        $this->expectException(TooManyObjectBuilderFactoryAttributes::class);
        $this->expectExceptionCode(1634044714);
        $this->expectExceptionMessage("Only one attribute of type `$factoryClass` is allowed, class `$objectClass` contains 2.");

        (new MapperBuilder())->mapper()->map($objectClass, 'foo');
    }
}

#[Attribute(Attribute::TARGET_CLASS)]
final class ForeignAttribute
{
}

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final class ObjectBuilderStrategyAttribute implements ObjectBuilderFactory
{
    public function for(ClassDefinition $class): array
    {
        return [new FakeObjectBuilder()];
    }
}

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

#[StaticMethodConstructor('failingConstructor')]
final class ObjectWithFailingBuilderStrategyAttribute
{
    public static function failingConstructor(): self
    {
        throw new FakeErrorMessage('some error message', 1656076067);
    }
}

#[ObjectBuilderStrategyAttribute]
#[ObjectBuilderStrategyAttribute]
final class ObjectWithSeveralBuilderStrategyAttributes
{
}
