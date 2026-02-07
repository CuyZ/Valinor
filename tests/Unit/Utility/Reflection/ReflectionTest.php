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

    public function test_function_reflection_is_created_only_once(): void
    {
        $function = fn () => 42;

        $functionReflectionA = Reflection::function($function);
        $functionReflectionB = Reflection::function($function);

        self::assertSame($functionReflectionA, $functionReflectionB);
    }
}
