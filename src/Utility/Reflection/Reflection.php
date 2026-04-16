<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Utility\Reflection;

use Closure;
use ReflectionClass;
use ReflectionFunction;
use UnitEnum;

use function class_exists;
use function enum_exists;
use function interface_exists;
use function ltrim;

/** @internal */
final class Reflection
{
    /** @var array<class-string, ReflectionClass<covariant object>> */
    private static array $classReflection = [];

    /** @var array<string, ReflectionFunction> */
    private static array $functionReflection = [];

    /** @var array<string, bool> */
    private static array $classOrInterfaceExists = [];

    /** @var array<string, bool> */
    private static array $enumExists = [];

    /**
     * Case-sensitive implementation of `class_exists` and `interface_exists`.
     *
     * @phpstan-assert-if-true class-string $name
     */
    public static function classOrInterfaceExists(string $name): bool
    {
        // @infection-ignore-all / We don't need to test the cache
        return self::$classOrInterfaceExists[$name] ??= (class_exists($name) || interface_exists($name))
            && self::class($name)->name === ltrim($name, '\\');
    }

    /**
     * @phpstan-assert-if-true class-string<UnitEnum> $name
     */
    public static function enumExists(string $name): bool
    {
        // @infection-ignore-all / We don't need to test the cache
        return self::$enumExists[$name] ??= enum_exists($name);
    }

    /**
     * @param class-string $className
     * @return ReflectionClass<covariant object>
     */
    public static function class(string $className): ReflectionClass
    {
        return self::$classReflection[$className] ??= new ReflectionClass($className);
    }

    public static function function(callable $function): ReflectionFunction
    {
        $closure = Closure::fromCallable($function);
        $reflection = new ReflectionFunction($closure);

        // @infection-ignore-all / We don't need to test the cache key strategy
        $fileName = $reflection->getFileName();

        // @infection-ignore-all / Built-in functions have no file; use the function name as key instead.
        $key = $fileName !== false
            ? $fileName . ':' . $reflection->getStartLine()
            : $reflection->getName();

        return self::$functionReflection[$key] ??= $reflection;
    }
}
