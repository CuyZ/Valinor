<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Utility\Reflection;

use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;
use SplFileObject;

use function is_file;

/**
 * @internal
 *
 * Imported from `doctrine/annotations`:
 * @link https://github.com/doctrine/annotations/blob/4858ab786a6cb568149209a9112dad3808c8a4de/lib/Doctrine/Common/Annotations/PhpParser.php
 */
final class PhpParser
{
    /** @var array<string, array<string, string>> */
    private static array $statements = [];

    /** @var array<string, string|null> */
    private static array $namespaces = [];

    /**
     * @param ReflectionClass<covariant object>|ReflectionFunction $reflection
     */
    public static function parseNamespace(ReflectionClass|ReflectionFunction $reflection): ?string
    {
        $signature = "{$reflection->getFileName()}:{$reflection->getStartLine()}";

        return self::$namespaces[$signature] ??= self::fetchNamespace($reflection);
    }

    /**
     * @param ReflectionClass<covariant object>|ReflectionFunction $reflection
     */
    private static function fetchNamespace(ReflectionClass|ReflectionFunction $reflection): ?string
    {
        $content = self::getFileContent($reflection);

        if ($content === null) {
            return null;
        }

        return (new NamespaceFinder())->findNamespace($content);
    }

    /**
     * @param ReflectionClass<covariant object>|ReflectionFunction|ReflectionMethod $reflection
     * @return array<string, string>
     */
    public static function parseUseStatements(ReflectionClass|ReflectionFunction|ReflectionMethod $reflection): array
    {
        $signature = "{$reflection->getFileName()}:{$reflection->getStartLine()}";

        return self::$statements[$signature] ??= self::fetchUseStatements($reflection);
    }

    /**
     * @param ReflectionClass<covariant object>|ReflectionFunction|ReflectionMethod $reflection
     * @return array<string, string>
     */
    private static function fetchUseStatements(ReflectionClass|ReflectionFunction|ReflectionMethod $reflection): array
    {
        $content = self::getFileContent($reflection);

        if ($content === null) {
            return [];
        }

        $tokenParser = new TokenParser($content);

        if ($reflection instanceof ReflectionMethod) {
            $reflection = $reflection->getDeclaringClass();
        }

        if ($reflection instanceof ReflectionFunction || $reflection->isAnonymous()) {
            // An anonymous class name carries either no namespace or the
            // extended/implemented type's namespace, not reliably the lexical one.
            $namespaceName = $tokenParser->getNamespace();
        } else {
            $namespaceName = $reflection->getNamespaceName();
        }

        return $tokenParser->parseUseStatements($namespaceName);
    }

    /**
     * @param ReflectionClass<covariant object>|ReflectionFunction|ReflectionMethod $reflection
     */
    private static function getFileContent(ReflectionClass|ReflectionFunction|ReflectionMethod $reflection): ?string
    {
        $filename = $reflection->getFileName();
        $startLine = $reflection->getStartLine();

        // @infection-ignore-all these values will never be `true`
        if ($filename === false || $startLine === false) {
            return null;
        }

        if (! is_file($filename)) {
            return null;
        }

        // @infection-ignore-all no need to test with `-1`
        $lineCnt = 0;
        $content = '';
        $file = new SplFileObject($filename);

        while (! $file->eof()) {
            if ($lineCnt++ === $startLine) {
                break;
            }

            $content .= $file->fgets();
        }

        return $content;
    }
}
