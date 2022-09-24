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
    /**
     * @template T of object
     *
     * @param ReflectionClass<T>|ReflectionFunction|ReflectionMethod $reflection
     * @return array<string, string>
     */
    public function parseUseStatements($reflection): array
    {
        $filename = $reflection->getFileName();

        if ($filename === false) {
            return [];
        }

        $content = $this->getFileContent($filename, (int)$reflection->getStartLine());

        if ($content === null) {
            return [];
        }

        $namespace = preg_quote($reflection->getNamespaceName());
        $content = preg_replace('/^.*?(\bnamespace\s+' . $namespace . '\s*[;{].*)$/s', '\\1', $content);
        $tokenizer = new TokenParser('<?php ' . $content);

        return $tokenizer->parseUseStatements($reflection->getNamespaceName());
    }

    private function getFileContent(string $filename, int $lineNumber): ?string
    {
        if (!is_file($filename)) {
            return null;
        }

        $content = '';
        $lineCnt = 0;
        $file = new SplFileObject($filename);
        while (!$file->eof()) {
            if ($lineCnt++ === $lineNumber) {
                break;
            }

            $content .= $file->fgets();
        }

        return $content;
    }
}
