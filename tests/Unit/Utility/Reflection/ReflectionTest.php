<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Utility\Reflection;

use CuyZ\Valinor\Tests\Unit\UnitTestCase;
use CuyZ\Valinor\Utility\Reflection\Reflection;
use stdClass;

final class ReflectionTest extends UnitTestCase
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

    public function test_get_function_reflection_returns_function_reflection(): void
    {
        $reflection = Reflection::function('strlen');

        self::assertSame('strlen', $reflection->getName());
    }

    public function test_function_reflection_is_created_for_each_call(): void
    {
        $function = fn () => 42;

        // Unlike class reflections, function reflections are intentionally not
        // memoized. The previous cache keyed on `spl_object_hash()`, which is
        // distinct for every closure instance, so it grew without bound.
        self::assertNotSame(
            Reflection::function($function),
            Reflection::function($function),
        );
    }
}
