<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Mapper\Object;

use CuyZ\Valinor\Mapper\Object\NativeConstructorObjectBuilder;
use CuyZ\Valinor\Mapper\Tree\Message\UserlandError;
use CuyZ\Valinor\Tests\Fake\Definition\FakeClassDefinition;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use RuntimeException;

final class NativeConstructorObjectBuilderTest extends TestCase
{
    public function test_arguments_instance_stays_the_same(): void
    {
        $object = new class () {
            public function __construct() {}
        };

        $class = FakeClassDefinition::fromReflection(new ReflectionClass($object));
        $objectBuilder = new NativeConstructorObjectBuilder($class);

        $argumentsA = $objectBuilder->describeArguments();
        $argumentsB = $objectBuilder->describeArguments();

        self::assertSame($argumentsA, $argumentsB);
    }

    public function test_build_object_with_constructor_returns_correct_object(): void
    {
        $object = new class ('foo', 'bar') {
            public function __construct(public string $valueA, public string $valueB, public string $valueC = 'Some parameter default value') {}
        };

        $class = FakeClassDefinition::fromReflection(new ReflectionClass($object));
        $objectBuilder = new NativeConstructorObjectBuilder($class);
        $result = $objectBuilder->build([
            'valueA' => 'valueA',
            'valueB' => 'valueB',
            'valueC' => 'valueC',
        ]);

        self::assertSame('valueA', $result->valueA); // @phpstan-ignore-line
        self::assertSame('valueB', $result->valueB); // @phpstan-ignore-line
        self::assertSame('valueC', $result->valueC); // @phpstan-ignore-line
    }

    public function test_exception_thrown_by_constructor_is_caught_and_wrapped(): void
    {
        $class = FakeClassDefinition::fromReflection(new ReflectionClass(SomeClassWithConstructorThatThrowsException::class));
        $objectBuilder = new NativeConstructorObjectBuilder($class);

        $this->expectException(UserlandError::class);

        $objectBuilder->build([]);
    }
}

final class SomeClassWithConstructorThatThrowsException
{
    public function __construct()
    {
        throw new RuntimeException('some exception', 1337);
    }
}
