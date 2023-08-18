<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Utility\Reflection;

use Closure;
use CuyZ\Valinor\Tests\Fixture\Object\ObjectWithPropertyWithNativeDisjunctiveNormalFormType;
use CuyZ\Valinor\Tests\Fixture\Object\ObjectWithPropertyWithNativeIntersectionType;
use CuyZ\Valinor\Tests\Fixture\Object\ObjectWithPropertyWithNativePhp82StandaloneTypes;
use CuyZ\Valinor\Utility\Reflection\Reflection;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionFunction;
use ReflectionProperty;
use ReflectionType;
use stdClass;

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
        $class = (new class () {
            public string $property;

            public function method(string $parameter): void {}
        })::class;

        $functions = require_once 'FakeFunctions.php';

        $reflectionClass = new ReflectionClass($class);
        $reflectionProperty = $reflectionClass->getProperty('property');
        $reflectionMethod = $reflectionClass->getMethod('method');
        $reflectionParameter = $reflectionMethod->getParameters()[0];
        $reflectionFunction = new ReflectionFunction(__NAMESPACE__ . '\some_function'); // PHP8.1 First-class callable syntax
        $reflectionFunctionMethod = new ReflectionFunction(Closure::fromCallable([self::class, 'test_reflection_signatures_are_correct'])); // PHP8.1 First-class callable syntax
        $reflectionFunctionOnOneLineClosure = new ReflectionFunction($functions['function_on_one_line']);
        $reflectionFunctionOnSeveralLinesClosure = new ReflectionFunction($functions['function_on_several_lines']);

        self::assertSame($class, Reflection::signature($reflectionClass));
        self::assertSame($class . '::$property', Reflection::signature($reflectionProperty));
        self::assertSame($class . '::method()', Reflection::signature($reflectionMethod));
        self::assertSame($class . '::method($parameter)', Reflection::signature($reflectionParameter));
        self::assertSame(__NAMESPACE__ . '\some_function()', Reflection::signature($reflectionFunction));
        self::assertSame(self::class . '::test_reflection_signatures_are_correct()', Reflection::signature($reflectionFunctionMethod));
        self::assertSame('Closure (line 8 of ' . __DIR__ . '/FakeFunctions.php)', Reflection::signature($reflectionFunctionOnOneLineClosure));
        self::assertSame('Closure (lines 9 to 15 of ' . __DIR__ . '/FakeFunctions.php)', Reflection::signature($reflectionFunctionOnSeveralLinesClosure));
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
            public ?string $someProperty = null;
        };

        /** @var ReflectionType $type */
        $type = (new ReflectionProperty($object, 'someProperty'))->getType();

        self::assertSame('string|null', Reflection::flattenType($type));
    }

    public function test_union_type_is_handled(): void
    {
        $class = new class () {
            public int|float $someProperty;
        };

        /** @var ReflectionType $type */
        $type = (new ReflectionProperty($class, 'someProperty'))->getType();

        self::assertSame('int|float', Reflection::flattenType($type));
    }

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

    /**
     * @requires PHP >= 8.2
     */
    public function test_disjunctive_normal_form_type_is_handled(): void
    {
        $class = ObjectWithPropertyWithNativeDisjunctiveNormalFormType::class;

        /** @var ReflectionType $type */
        $type = (new ReflectionProperty($class, 'someProperty'))->getType();

        self::assertSame('Countable&Iterator|Countable&DateTime', Reflection::flattenType($type));
    }

    /**
     * @requires PHP >= 8.2
     */
    public function test_native_null_type_is_handled(): void
    {
        $class = ObjectWithPropertyWithNativePhp82StandaloneTypes::class;

        /** @var ReflectionType $type */
        $type = (new ReflectionProperty($class, 'nativeNull'))->getType();

        self::assertSame('null', Reflection::flattenType($type));
    }

    /**
     * @requires PHP >= 8.2
     */
    public function test_native_true_type_is_handled(): void
    {
        $class = ObjectWithPropertyWithNativePhp82StandaloneTypes::class;

        /** @var ReflectionType $type */
        $type = (new ReflectionProperty($class, 'nativeTrue'))->getType();

        self::assertSame('true', Reflection::flattenType($type));
    }

    /**
     * @requires PHP >= 8.2
     */
    public function test_native_false_type_is_handled(): void
    {
        $class = ObjectWithPropertyWithNativePhp82StandaloneTypes::class;

        /** @var ReflectionType $type */
        $type = (new ReflectionProperty($class, 'nativeFalse'))->getType();

        self::assertSame('false', Reflection::flattenType($type));
    }
}

function some_function(): void {}
