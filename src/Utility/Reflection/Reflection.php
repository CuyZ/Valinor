<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Utility\Reflection;

use Attribute;
use Closure;
use Error;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;
use Reflector;
use UnitEnum;

use function array_filter;
use function array_map;
use function class_exists;
use function enum_exists;
use function interface_exists;
use function ltrim;
use function spl_object_hash;

/** @internal */
final class Reflection
{
    /** @var array<class-string, ReflectionClass<object>> */
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
     * @return ReflectionClass<object>
     */
    public static function class(string $className): ReflectionClass
    {
        return self::$classReflection[$className] ??= new ReflectionClass($className);
    }

    public static function function(callable $function): ReflectionFunction
    {
        $closure = Closure::fromCallable($function);

        return self::$functionReflection[spl_object_hash($closure)] ??= new ReflectionFunction($closure);
    }

    /**
     * @param ReflectionClass<object>|ReflectionProperty|ReflectionMethod|ReflectionFunction|ReflectionParameter $reflection
     * @return array<ReflectionAttribute<object>>
     */
    public static function attributes(Reflector $reflection): array
    {
        $attributes = array_filter(
            $reflection->getAttributes(),
            static fn (ReflectionAttribute $attribute) => $attribute->getName() !== Attribute::class,
        );

        return array_filter(
            array_map(
                static function (ReflectionAttribute $attribute) {
                    try {
                        $attribute->newInstance();

                        return $attribute;
                    } catch (Error) {
                        // Race condition when the attribute is affected to a property/parameter
                        // that was PROMOTED, in this case the attribute will be applied to both
                        // ParameterReflection AND PropertyReflection, BUT the target arg inside the attribute
                        // class is configured to support only ONE of them (parameter OR property)
                        // https://wiki.php.net/rfc/constructor_promotion#attributes for more details.
                        // Ignore attribute if the instantiation failed.
                        return null;
                    }
                },
                $attributes,
            ),
        );
    }
}
