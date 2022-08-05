<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Mapper\Object;

use CuyZ\Valinor\Mapper\Object\Exception\ConstructorMethodIsNotPublic;
use CuyZ\Valinor\Mapper\Object\Exception\ConstructorMethodIsNotStatic;
use CuyZ\Valinor\Mapper\Object\Exception\MethodNotFound;
use CuyZ\Valinor\Mapper\Object\MethodObjectBuilder;
use CuyZ\Valinor\Mapper\Tree\Message\UserlandError;
use CuyZ\Valinor\Tests\Fake\Definition\FakeClassDefinition;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use RuntimeException;
use stdClass;

final class MethodObjectBuilderTest extends TestCase
{
    public function test_signature_is_method_signature(): void
    {
        $object = new class () {
            public static function someMethod(): stdClass
            {
                return new stdClass();
            }
        };

        $class = FakeClassDefinition::fromReflection(new ReflectionClass($object));
        $objectBuilder = new MethodObjectBuilder($class, 'someMethod');

        self::assertSame('Signature::someMethod', $objectBuilder->signature());
    }

    public function test_not_existing_method_throws_exception(): void
    {
        $this->expectException(MethodNotFound::class);
        $this->expectExceptionCode(1634044209);
        $this->expectExceptionMessage('Method `notExistingMethod` was not found in class `stdClass`.');

        $class = FakeClassDefinition::fromReflection(new ReflectionClass(stdClass::class));
        new MethodObjectBuilder($class, 'notExistingMethod');
    }

    public function test_invalid_constructor_method_throws_exception(): void
    {
        $this->expectException(ConstructorMethodIsNotStatic::class);
        $this->expectExceptionCode(1634044370);
        $this->expectExceptionMessage('Invalid constructor method `Signature::invalidConstructor`: it is neither the constructor nor a static constructor.');

        $object = new class () {
            public function invalidConstructor(): void
            {
            }
        };

        $class = FakeClassDefinition::fromReflection(new ReflectionClass($object));
        new MethodObjectBuilder($class, 'invalidConstructor');
    }

    public function test_exception_thrown_by_method_is_caught_and_wrapped(): void
    {
        $class = new class () {
            public static function someMethod(): stdClass
            {
                throw new RuntimeException('some exception', 1337);
            }
        };

        $class = FakeClassDefinition::fromReflection(new ReflectionClass($class));
        $objectBuilder = new MethodObjectBuilder($class, 'someMethod');

        $this->expectException(UserlandError::class);

        $objectBuilder->build([]);
    }

    public function test_constructor_builder_for_class_with_private_constructor_throws_exception(): void
    {
        $this->expectException(ConstructorMethodIsNotPublic::class);
        $this->expectExceptionCode(1630937169);
        $this->expectExceptionMessage('The constructor of the class `' . ObjectWithPrivateNativeConstructor::class . '` is not public.');

        $class = FakeClassDefinition::fromReflection(new ReflectionClass(ObjectWithPrivateNativeConstructor::class));
        new MethodObjectBuilder($class, '__construct');
    }

    public function test_constructor_builder_for_class_with_private_named_constructor_throws_exception(): void
    {
        $classWithPrivateNativeConstructor = new class () {
            // @phpstan-ignore-next-line
            private static function someConstructor(): void
            {
            }
        };

        $this->expectException(ConstructorMethodIsNotPublic::class);
        $this->expectExceptionCode(1630937169);
        $this->expectExceptionMessage('The named constructor `Signature::someConstructor` is not public.');

        $class = FakeClassDefinition::fromReflection(new ReflectionClass($classWithPrivateNativeConstructor));
        new MethodObjectBuilder($class, 'someConstructor');
    }

    public function test_arguments_instance_stays_the_same(): void
    {
        $class = new class () {
            public static function someMethod(string $string): stdClass
            {
                $class = new stdClass();
                $class->string = $string;

                return $class;
            }
        };
        $class = FakeClassDefinition::fromReflection(new ReflectionClass($class));

        $objectBuilder = new MethodObjectBuilder($class, 'someMethod');

        $argumentsA = $objectBuilder->describeArguments();
        $argumentsB = $objectBuilder->describeArguments();

        self::assertSame($argumentsA, $argumentsB);
    }
}

final class ObjectWithPrivateNativeConstructor
{
    private function __construct()
    {
    }
}
