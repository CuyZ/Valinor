<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Utility\Reflection;

use Closure;
use ReflectionClass;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionIntersectionType;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionProperty;
use ReflectionType;
use ReflectionUnionType;
use Reflector;
use RuntimeException;

use function get_class;
use function implode;
use function preg_match;
use function trim;

final class Reflection
{
    /** @var array<ReflectionClass<object>> */
    private static array $classReflection = [];

    /**
     * @param class-string $className
     * @return ReflectionClass<object>
     */
    public static function class(string $className): ReflectionClass
    {
        return self::$classReflection[$className] ??= new ReflectionClass($className);
    }

    public static function signature(Reflector $reflection): string
    {
        if ($reflection instanceof ReflectionClass) {
            return $reflection->name;
        }

        if ($reflection instanceof ReflectionProperty) {
            return "{$reflection->getDeclaringClass()->name}::\$$reflection->name";
        }

        if ($reflection instanceof ReflectionMethod) {
            return "{$reflection->getDeclaringClass()->name}::$reflection->name()";
        }

        if ($reflection instanceof ReflectionParameter) {
            $signature = $reflection->getDeclaringFunction()->name . "(\$$reflection->name)";
            $class = $reflection->getDeclaringClass();

            if ($class) {
                $signature = $class->name . '::' . $signature;
            }

            return $signature;
        }

        throw new RuntimeException('Invalid reflection type `' . get_class($reflection) . '`.');
    }

    public static function flattenType(ReflectionType $type): string
    {
        if ($type instanceof ReflectionUnionType) {
            return implode('|', $type->getTypes());
        }

        if ($type instanceof ReflectionIntersectionType) {
            return implode('&', $type->getTypes());
        }

        /** @var ReflectionNamedType $type */
        $name = $type->getName();

        if ($name !== 'null' && $type->allowsNull()) {
            return $name . '|null';
        }

        return $name;
    }

    /**
     * @param ReflectionProperty|ReflectionParameter $reflection
     */
    public static function docBlockType(Reflector $reflection): ?string
    {
        if ($reflection instanceof ReflectionProperty) {
            $docComment = $reflection->getDocComment() ?: '';
            $regex = '@var\s+([\w\s?|&<>\'",-\[\]{}:\\\\]+)';
        } else {
            $docComment = $reflection->getDeclaringFunction()->getDocComment() ?: '';
            $regex = "@param\s+([\w\s?|&<>'\",-\[\]{}:\\\\]+)\s+\\$$reflection->name\s+";
        }

        if (! preg_match("/$regex/", $docComment, $matches)) {
            return null;
        }

        return $matches[1];
    }

    public static function docBlockReturnType(ReflectionFunctionAbstract $reflection): ?string
    {
        $docComment = $reflection->getDocComment() ?: '';

        if (! preg_match('/@return\s+([\w\s?|&<>\'",-\[\]{}:\\\\]+)/', $docComment, $matches)) {
            return null;
        }

        return trim($matches[1]);
    }

    public static function ofCallable(callable $callable): ReflectionFunctionAbstract
    {
        if ($callable instanceof Closure) {
            return new ReflectionFunction($callable);
        }

        if (is_string($callable)) {
            $parts = explode('::', $callable);

            return count($parts) > 1
                ? new ReflectionMethod($parts[0], $parts[1])
                : new ReflectionFunction($callable);
        }

        if (! is_array($callable)) {
            $callable = [$callable, '__invoke'];
        }

        return new ReflectionMethod($callable[0], $callable[1]);
    }
}
