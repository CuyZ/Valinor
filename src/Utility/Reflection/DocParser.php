<?php

namespace CuyZ\Valinor\Utility\Reflection;

use CuyZ\Valinor\Type\Parser\Exception\Template\DuplicatedTemplateName;
use ReflectionClass;
use ReflectionFunctionAbstract;
use ReflectionParameter;
use ReflectionProperty;

use function end;
use function preg_match;
use function preg_match_all;
use function str_replace;
use function str_split;
use function strrpos;
use function substr;

/** @internal */
final class DocParser
{
    public static function propertyType(ReflectionProperty $reflection): ?string
    {
        $doc = self::sanitizeDocComment($reflection->getDocComment());

        if ($doc === null) {
            return null;
        }

        return self::annotationType($doc, 'var');
    }

    public static function parameterType(ReflectionParameter $reflection): ?string
    {
        $doc = self::sanitizeDocComment($reflection->getDeclaringFunction()->getDocComment());

        if ($doc === null) {
            return null;
        }

        if (! preg_match("/(?<type>.*)\\$$reflection->name(\s|\z)/s", $doc, $matches)) {
            return null;
        }

        return self::annotationType($matches['type'], 'param');
    }

    public static function functionReturnType(ReflectionFunctionAbstract $reflection): ?string
    {
        $doc = self::sanitizeDocComment($reflection->getDocComment());

        if ($doc === null) {
            return null;
        }

        return self::annotationType($doc, 'return');
    }

    /**
     * @param ReflectionClass<object> $reflection
     * @return array<string, string>
     */
    public static function localTypeAliases(ReflectionClass $reflection): array
    {
        $doc = self::sanitizeDocComment($reflection->getDocComment());

        if ($doc === null) {
            return [];
        }

        $types = [];

        preg_match_all('/@(phpstan|psalm)-type\s+(?<name>[a-zA-Z]\w*)\s*=?\s*(?<type>.*)/', $doc, $matches);

        foreach ($matches['name'] as $key => $name) {
            /** @var string $name */
            $types[$name] = self::findType($matches['type'][$key]);
        }

        return $types;
    }

    /**
     * @param ReflectionClass<object> $reflection
     * @return array<class-string, string[]>
     */
    public static function importedTypeAliases(ReflectionClass $reflection): array
    {
        $doc = self::sanitizeDocComment($reflection->getDocComment());

        if ($doc === null) {
            return [];
        }

        $types = [];

        preg_match_all('/@(phpstan|psalm)-import-type\s+(?<name>[a-zA-Z]\w*)\s*from\s*(?<class>\w+)/', $doc, $matches);

        foreach ($matches['name'] as $key => $name) {
            /** @var class-string $class */
            $class = $matches['class'][$key];

            $types[$class][] = $name;
        }

        return $types;
    }

    /**
     * @param ReflectionClass<object> $reflection
     * @return array<string>
     */
    public static function classExtendsTypes(ReflectionClass $reflection): array
    {
        $doc = self::sanitizeDocComment($reflection->getDocComment());

        if ($doc === null) {
            return [];
        }

        preg_match_all('/@(phpstan-|psalm-)?extends\s+(?<type>.+)/', $doc, $matches);

        return $matches['type'];
    }

    /**
     * @param ReflectionClass<object> $reflection
     * @return array<string, string>
     */
    public static function classTemplates(ReflectionClass $reflection): array
    {
        $doc = self::sanitizeDocComment($reflection->getDocComment());

        if ($doc === null) {
            return [];
        }

        $templates = [];

        preg_match_all("/@(phpstan-|psalm-)?template\s+(?<name>\w+)(\s+of\s+(?<type>.+))?/", $doc, $matches);

        foreach ($matches['name'] as $key => $name) {
            /** @var string $name */
            if (isset($templates[$name])) {
                throw new DuplicatedTemplateName($reflection->name, $name);
            }

            $templates[$name] = self::findType($matches['type'][$key]);
        }

        return $templates;
    }

    private static function annotationType(string $string, string $annotation): ?string
    {
        foreach (["@phpstan-$annotation", "@psalm-$annotation", "@$annotation"] as $case) {
            $pos = strrpos($string, $case);

            if ($pos !== false) {
                return self::findType(substr($string, $pos + strlen($case)));
            }
        }

        return null;
    }

    private static function findType(string $string): string
    {
        $operatorsMatrix = [
            '{' => '}',
            '<' => '>',
            '"' => '"',
            "'" => "'",
        ];

        $type = '';
        $operators = [];
        $expectExpression = true;

        $string = str_replace("\n", ' ', $string);
        $chars = str_split($string);

        foreach ($chars as $key => $char) {
            if ($operators === []) {
                if ($char === '|' || $char === '&') {
                    $expectExpression = true;
                } elseif (! $expectExpression && $chars[$key - 1] === ' ') {
                    if ($char === '.' && $chars[$key+1] === '.' && $chars[$key+2] === '.') {
                        return trim($type) . '[]';
                    }

                    break;
                } elseif ($char !== ' ') {
                    $expectExpression = false;
                }
            }

            if (isset($operatorsMatrix[$char])) {
                $operators[] = $operatorsMatrix[$char];
            } elseif ($operators !== [] && $char === end($operators)) {
                array_pop($operators);
            }

            $type .= $char;
        }

        return trim($type);
    }

    private static function sanitizeDocComment(string|false $doc): ?string
    {
        /** @infection-ignore-all mutating `$doc` to `true` makes no sense */
        if ($doc === false) {
            return null;
        }

        $doc = preg_replace('#^\s*/\*\*([^/]+)\*/\s*$#', '$1', $doc);

        return preg_replace('/^\s*\*\s*(\S*)/m', '$1', $doc); // @phpstan-ignore-line
    }
}
