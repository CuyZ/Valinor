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
use function preg_match_all;
use function preg_replace;
use function spl_object_hash;
use function trim;

/** @internal */
final class Reflection
{
    private const TOOL_NONE = '';
    private const TOOL_EXPRESSION = '((?<tool>psalm|phpstan)-)';
    private const TYPE_EXPRESSION = '(?<type>[\w\s?|&<>\'",-:\\\\\[\]{}]+)';

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

        if ($name !== 'null' && $type->allowsNull() && $name !== 'mixed') {
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
            return self::parseDocBlock(
                self::sanitizeDocComment($reflection),
                sprintf('@%s?var\s+%s', self::TOOL_EXPRESSION, self::TYPE_EXPRESSION)
            );
        }

        if (method_exists($reflection, 'isPromoted') && $reflection->isPromoted()) {
            $type = self::parseDocBlock(
                self::sanitizeDocComment($reflection->getDeclaringClass()->getProperty($reflection->name)),
                sprintf('@%s?var\s+%s', self::TOOL_EXPRESSION, self::TYPE_EXPRESSION)
            );

            if ($type !== null) {
                return $type;
            }
        }

        return self::parseDocBlock(
            self::sanitizeDocComment($reflection->getDeclaringFunction()),
            sprintf('@%s?param\s+%s\s+\$\b%s\b', self::TOOL_EXPRESSION, self::TYPE_EXPRESSION, $reflection->name)
        );
    }

    private static function parseDocBlock(string $docComment, string $expression): ?string
    {
        if (! preg_match_all("/$expression/", $docComment, $matches)) {
            return null;
        }

        foreach ($matches['tool'] as $index => $tool) {
            if ($tool === self::TOOL_NONE) {
                continue;
            }

            return trim($matches['type'][$index]);
        }

        return trim($matches['type'][0]);
    }

    public static function docBlockReturnType(ReflectionFunctionAbstract $reflection): ?string
    {
        $docComment = self::sanitizeDocComment($reflection);

        $expression = sprintf('/@%s?return\s+%s/', self::TOOL_EXPRESSION, self::TYPE_EXPRESSION);

        if (! preg_match_all($expression, $docComment, $matches)) {
            return null;
        }

        foreach ($matches['tool'] as $index => $tool) {
            if ($tool === self::TOOL_NONE) {
                continue;
            }

            return trim($matches['type'][$index]);
        }

        return trim($matches['type'][0]);
    }

    /**
     * @param ReflectionClass<object> $reflection
     * @return array<string, string>
     */
    public static function localTypeAliases(ReflectionClass $reflection): array
    {
        $types = [];
        $docComment = self::sanitizeDocComment($reflection);

        $expression = sprintf('/@(phpstan|psalm)-type\s+([a-zA-Z]\w*)\s*=?\s*%s/', self::TYPE_EXPRESSION);

        preg_match_all($expression, $docComment, $matches);

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

        $expression = sprintf('/@(phpstan|psalm)-import-type\s+([a-zA-Z]\w*)\s*from\s*%s/', self::TYPE_EXPRESSION);
        preg_match_all($expression, $docComment, $matches);

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

        return trim(preg_replace('/\s*\*\s*(\S*)/', "\n\$1", $docComment)); // @phpstan-ignore-line
    }
}
