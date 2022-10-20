<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Utility\Reflection;

use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;
use SplFileObject;

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

    /**
     * @param ReflectionClass<object>|ReflectionFunction|ReflectionMethod $reflection
     * @return array<string, string>
     */
    public static function parseUseStatements(\ReflectionClass|\ReflectionFunction|\ReflectionMethod $reflection): array
    {
        // @infection-ignore-all
        return self::$statements[Reflection::signature($reflection)] ??= self::fetchUseStatements($reflection);
    }

    /**
     * @param ReflectionClass<object>|ReflectionFunction|ReflectionMethod $reflection
     * @return array<string, string>
     */
    private static function fetchUseStatements(\ReflectionClass|\ReflectionFunction|\ReflectionMethod $reflection): array
    {
        $filename = $reflection->getFileName();
        $startLine = $reflection->getStartLine();

        if ($reflection instanceof ReflectionMethod) {
            $namespaceName = $reflection->getDeclaringClass()->getNamespaceName();
        } elseif ($reflection instanceof ReflectionFunction && $reflection->getClosureScopeClass()) {
            $namespaceName = $reflection->getClosureScopeClass()->getNamespaceName();
        } else {
            $namespaceName = $reflection->getNamespaceName();
        }

        // @infection-ignore-all these values will never be `true`
        if ($filename === false || $startLine === false) {
            return [];
        }

        if (! is_file($filename)) {
            return [];
        }

        $content = self::getFileContent($filename, $startLine);

        return (new TokenParser($content))->parseUseStatements($namespaceName);
    }

    private static function getFileContent(string $filename, int $lineNumber): string
    {
        // @infection-ignore-all no need to test with `-1`
        $lineCnt = 0;
        $content = '';
        $file = new SplFileObject($filename);

        while (! $file->eof()) {
            if ($lineCnt++ === $lineNumber) {
                break;
            }

            $content .= $file->fgets();
        }

        return $content;
    }
}
