<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Mapper\Object;

use CuyZ\Valinor\Mapper\Object\Argument;
use CuyZ\Valinor\Mapper\Object\Exception\ConstructorMethodIsNotPublic;
use CuyZ\Valinor\Mapper\Object\Exception\ConstructorMethodIsNotStatic;
use CuyZ\Valinor\Mapper\Object\Exception\InvalidConstructorMethodClassReturnType;
use CuyZ\Valinor\Mapper\Object\Exception\InvalidConstructorMethodReturnType;
use CuyZ\Valinor\Mapper\Object\Exception\InvalidSourceForObject;
use CuyZ\Valinor\Mapper\Object\Exception\MethodNotFound;
use CuyZ\Valinor\Mapper\Object\Exception\MissingMethodArgument;
use CuyZ\Valinor\Mapper\Object\Exception\ObjectConstructionError;
use CuyZ\Valinor\Mapper\Object\MethodObjectBuilder;
use CuyZ\Valinor\Tests\Fake\Definition\FakeClassDefinition;
use Generator;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use RuntimeException;
use stdClass;

use function get_class;

final class MethodObjectBuilderTest extends TestCase
{
    public function test_build_object_with_constructor_returns_correct_object(): void
    {
        $object = new class ('foo', 'bar') {
            public string $valueA;

            public string $valueB;

            public string $valueC;

            public function __construct(
                string $valueA,
                string $valueB,
                string $valueC = 'Some parameter default value'
            ) {
                $this->valueA = $valueA;
                $this->valueB = $valueB;
                $this->valueC = $valueC;
            }
        };

        $class = FakeClassDefinition::fromReflection(new ReflectionClass($object));
        $objectBuilder = new MethodObjectBuilder($class, '__construct');
        $result = $objectBuilder->build([
            'valueA' => 'valueA',
            'valueB' => 'valueB',
            'valueC' => 'valueC',
        ]);

        self::assertSame('valueA', $result->valueA); // @phpstan-ignore-line
        self::assertSame('valueB', $result->valueB); // @phpstan-ignore-line
        self::assertSame('valueC', $result->valueC); // @phpstan-ignore-line
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

    public function test_invalid_constructor_method_return_type_throws_exception(): void
    {
        $object = new class () {
            public static function invalidConstructor(): int
            {
                return 42;
            }
        };

        $this->expectException(InvalidConstructorMethodReturnType::class);
        $this->expectExceptionCode(1638094383);
        $this->expectExceptionMessage('Method `Signature::invalidConstructor` must return `' . get_class($object) . '` to be a valid constructor.');

        $class = FakeClassDefinition::fromReflection(new ReflectionClass($object));
        new MethodObjectBuilder($class, 'invalidConstructor');
    }

    public function test_invalid_constructor_method_class_return_type_throws_exception(): void
    {
        $object = new class () {
            public static function invalidConstructor(): stdClass
            {
                return new stdClass();
            }
        };

        $this->expectException(InvalidConstructorMethodClassReturnType::class);
        $this->expectExceptionCode(1638094499);
        $this->expectExceptionMessage('Method `Signature::invalidConstructor` must return `' . get_class($object) . '` to be a valid constructor but returns `stdClass`.');

        $class = FakeClassDefinition::fromReflection(new ReflectionClass($object));
        new MethodObjectBuilder($class, 'invalidConstructor');
    }

    public function test_invalid_source_type_throws_exception(): void
    {
        $object = new class () {
            public function __construct()
            {
            }
        };

        $class = FakeClassDefinition::fromReflection(new ReflectionClass($object));
        $objectBuilder = new MethodObjectBuilder($class, '__construct');

        $this->expectException(InvalidSourceForObject::class);
        $this->expectExceptionCode(1632903281);
        $this->expectExceptionMessage('Invalid source type `string`, it must be an iterable.');

        /** @var Generator<Argument> $arguments */
        $arguments = $objectBuilder->describeArguments('foo');
        $arguments->current();
    }

    public function test_missing_arguments_throws_exception(): void
    {
        $object = new class ('foo') {
            public string $value;

            public function __construct(string $value)
            {
                $this->value = $value;
            }
        };

        $class = FakeClassDefinition::fromReflection(new ReflectionClass($object));
        $objectBuilder = new MethodObjectBuilder($class, '__construct');

        $this->expectException(MissingMethodArgument::class);
        $this->expectExceptionCode(1629468609);
        $this->expectExceptionMessage('Missing argument `Signature::value` of type `string`.');

        $objectBuilder->build([]);
    }

    public function test_exception_thrown_by_constructor_is_caught_and_wrapped(): void
    {
        $class = FakeClassDefinition::fromReflection(new ReflectionClass(ObjectWithConstructorThatThrowsException::class));
        $objectBuilder = new MethodObjectBuilder($class, '__construct');

        $this->expectException(ObjectConstructionError::class);
        $this->expectExceptionCode(1630142421);
        $this->expectExceptionMessage('some exception');

        $objectBuilder->build([]);
    }

    public function test_constructor_builder_for_class_with_private_constructor_throws_exception(): void
    {
        $this->expectException(ConstructorMethodIsNotPublic::class);
        $this->expectExceptionCode(1630937169);
        $this->expectExceptionMessage('The constructor method `Signature::someConstructor` must be public.');

        $class = FakeClassDefinition::fromReflection(new ReflectionClass(ObjectWithPrivateConstructor::class));
        new MethodObjectBuilder($class, 'someConstructor');
    }
}

final class ObjectWithPrivateConstructor
{
    // @phpstan-ignore-next-line
    private static function someConstructor(): void
    {
    }
}

final class ObjectWithConstructorThatThrowsException
{
    public function __construct()
    {
        throw new RuntimeException('some exception');
    }
}
