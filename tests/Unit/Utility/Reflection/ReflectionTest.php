<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Utility\Reflection;

use CuyZ\Valinor\Tests\Fake\FakeReflector;
use CuyZ\Valinor\Tests\Fixture\Object\ObjectWithPropertyWithNativeIntersectionType;
use CuyZ\Valinor\Tests\Fixture\Object\ObjectWithPropertyWithNativeUnionType;
use CuyZ\Valinor\Utility\Reflection\Reflection;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionFunction;
use ReflectionProperty;
use ReflectionType;
use RuntimeException;
use stdClass;

use function get_class;

final class ReflectionTest extends TestCase
{
    public function test_get_class_reflection_returns_class_reflection(): void
    {
        $className = stdClass::class;
        $classReflection = Reflection::class($className);

        self::assertSame($className, $classReflection->getName());
    }

    public function test_class_reflection_is_created_only_once(): void
    {
        $className = stdClass::class;
        $classReflectionA = Reflection::class($className);
        $classReflectionB = Reflection::class($className);

        self::assertSame($classReflectionA, $classReflectionB);
    }

    public function test_function_reflection_is_created_only_once(): void
    {
        $function = fn () => 42;

        $functionReflectionA = Reflection::function($function);
        $functionReflectionB = Reflection::function($function);

        self::assertSame($functionReflectionA, $functionReflectionB);
    }

    public function test_reflection_signatures_are_correct(): void
    {
        $class = get_class(new class () {
            public string $property;

            public function method(string $parameter): void
            {
            }
        });

        $reflectionClass = new ReflectionClass($class);
        $reflectionProperty = $reflectionClass->getProperty('property');
        $reflectionMethod = $reflectionClass->getMethod('method');
        $reflectionParameter = $reflectionMethod->getParameters()[0];

        self::assertSame($class, Reflection::signature($reflectionClass));
        self::assertSame($class . '::$property', Reflection::signature($reflectionProperty));
        self::assertSame($class . '::method()', Reflection::signature($reflectionMethod));
        self::assertSame($class . '::method($parameter)', Reflection::signature($reflectionParameter));
    }

    public function test_invalid_reflection_signature_throws_exception(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid reflection type `' . FakeReflector::class . '`.');

        $wrongReflector = new FakeReflector();

        Reflection::signature($wrongReflector);
    }

    public function test_scalar_type_is_handled(): void
    {
        $object = new class () {
            public string $someProperty;
        };

        /** @var ReflectionType $type */
        $type = (new ReflectionProperty($object, 'someProperty'))->getType();

        self::assertSame('string', Reflection::flattenType($type));
    }

    public function test_nullable_scalar_type_is_handled(): void
    {
        $object = new class () {
            public ?string $someProperty;
        };

        /** @var ReflectionType $type */
        $type = (new ReflectionProperty($object, 'someProperty'))->getType();

        self::assertSame('string|null', Reflection::flattenType($type));
    }

    /**
     * @requires PHP >= 8
     */
    public function test_union_type_is_handled(): void
    {
        $class = ObjectWithPropertyWithNativeUnionType::class;

        /** @var ReflectionType $type */
        $type = (new ReflectionProperty($class, 'someProperty'))->getType();

        self::assertSame('int|float', Reflection::flattenType($type));
    }

    /**
     * @requires PHP >= 8
     */
    public function test_mixed_type_is_handled(): void
    {
        $object = new class () {
            public mixed $someProperty;
        };

        /** @var ReflectionType $type */
        $type = (new ReflectionProperty($object, 'someProperty'))->getType();
        self::assertSame('mixed', Reflection::flattenType($type));
    }

    /**
     * @requires PHP >= 8.1
     */
    public function test_intersection_type_is_handled(): void
    {
        $class = ObjectWithPropertyWithNativeIntersectionType::class;

        /** @var ReflectionType $type */
        $type = (new ReflectionProperty($class, 'someProperty'))->getType();

        self::assertSame('Countable&Iterator', Reflection::flattenType($type));
    }

    public function test_docblock_return_type_is_fetched_correctly(): void
    {
        $callable =
            /**
             * @return int
             */
            static function () {
                return 42;
            };

        $type = Reflection::docBlockReturnType(new ReflectionFunction($callable));

        self::assertSame('int', $type);
    }

    public function test_docblock_return_type_with_no_docblock_returns_null(): void
    {
        $callable = static function (): void {
        };

        $type = Reflection::docBlockReturnType(new ReflectionFunction($callable));

        self::assertNull($type);
    }

    public function test_reflection_of_closure_is_correct(): void
    {
        $callable = static function (): void {
        };

        $reflection = Reflection::ofCallable($callable);

        self::assertTrue($reflection->isClosure());
    }

    public function test_reflection_of_function_is_correct(): void
    {
        $reflection = Reflection::ofCallable('strlen');

        self::assertSame('strlen', $reflection->getShortName());
    }

    public function test_reflection_of_static_method_is_correct(): void
    {
        $class = new class () {
            public static function someMethod(): void
            {
            }
        };

        // @phpstan-ignore-next-line
        $reflection = Reflection::ofCallable(get_class($class) . '::someMethod');

        self::assertSame('someMethod', $reflection->getShortName());
    }

    public function test_reflection_of_callable_class_is_correct(): void
    {
        $class = new class () {
            public function __invoke(): void
            {
            }
        };

        $reflection = Reflection::ofCallable($class);

        self::assertSame('__invoke', $reflection->getShortName());
    }
}
