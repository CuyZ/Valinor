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
use function preg_match_all;
use function preg_replace;
use function spl_object_hash;
use function trim;

/** @internal */
final class Reflection
{
    /** @var array<class-string, ReflectionClass<object>> */
    private static array $classReflection = [];

    /** @var array<string, ReflectionFunction> */
    private static array $functionReflection = [];

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

        if ($reflection instanceof ReflectionFunction) {
            return "$reflection->name:{$reflection->getStartLine()}-{$reflection->getEndLine()}";
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
            $docComment = self::sanitizeDocComment($reflection);
            $regex = "@var\s+([\w\s?|&<>'\",-:\\\\\[\]{}]+)";
        } else {
            $docComment = self::sanitizeDocComment($reflection->getDeclaringFunction());
            $regex = "@param\s+([\w\s?|&<>'\",-:\\\\\[\]{}]+)\s+\\$$reflection->name(\W+|$)";
        }

        if (! preg_match("/$regex/", $docComment, $matches)) {
            return null;
        }

        return $matches[1];
    }

    public static function docBlockReturnType(ReflectionFunctionAbstract $reflection): ?string
    {
        $docComment = self::sanitizeDocComment($reflection);

        if (! preg_match("/@return\s+([\w\s?|&<>'\",-:\\\\\[\]{}]+)(\W*|$)/", $docComment, $matches)) {
            return null;
        }

        return trim($matches[1]);
    }

    /**
     * @param ReflectionClass<object> $reflection
     * @return array<string, string>
     */
    public static function localTypeAliases(ReflectionClass $reflection): array
    {
        $types = [];
        $docComment = self::sanitizeDocComment($reflection);

        preg_match_all('/@(phpstan|psalm)-type\s+([a-zA-Z]\w*)\s*=?\s*([\w\s?|&<>\'",-:\\\\\[\]{}]+)/', $docComment, $matches);

        foreach ($matches[2] as $key => $name) {
            $types[(string)$name] = $matches[3][$key];
        }

        return $types;
    }

    /**
     * @param ReflectionClass<object> $reflection
     * @return array<class-string, string[]>
     */
    public static function importedTypeAliases(ReflectionClass $reflection): array
    {
        $types = [];
        $docComment = self::sanitizeDocComment($reflection);

        preg_match_all('/@(phpstan|psalm)-import-type\s+([a-zA-Z]\w*)\s*from\s*([\w\s?|&<>\'",-:\\\\\[\]{}]+)/', $docComment, $matches);

        foreach ($matches[2] as $key => $name) {
            /** @var class-string $classString */
            $classString = $matches[3][$key];

            $types[$classString][] = $name;
        }

        return $types;
    }

    /**
     * @param ReflectionClass<object>|ReflectionProperty|ReflectionFunctionAbstract $reflection
     */
    private static function sanitizeDocComment(Reflector $reflection): string
    {
        $docComment = preg_replace('#^\s*/\*\*([^/]+)/\s*$#', '$1', $reflection->getDocComment() ?: '');

        return preg_replace('/\s*\*\s*(\S*)/', '$1', $docComment); // @phpstan-ignore-line
    }
}
