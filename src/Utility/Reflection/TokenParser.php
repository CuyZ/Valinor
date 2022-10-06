<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Utility\Reflection;

/**
 * @internal
 *
 * Imported from `doctrine/annotations`:
 * @link https://github.com/doctrine/annotations/blob/de990c9a69782a8b15fa8c9248de0ef4d82ed701/lib/Doctrine/Common/Annotations/TokenParser.php
 */
final class TokenParser
{
    /** @var array<int, array{0: int, 1: string}|string> */
    private array $tokens;

    private int $numTokens;

    private int $pointer = 0;

    public function __construct(string $content)
    {
        $this->tokens = token_get_all($content);

        /** @see https://github.com/doctrine/annotations/blob/4858ab786a6cb568149209a9112dad3808c8a4de/lib/Doctrine/Common/Annotations/TokenParser.php#L55-L61 */
        // @infection-ignore-all
        token_get_all("<?php\n/**\n *\n */"); // @phpstan-ignore-line

        $this->numTokens = count($this->tokens);
    }

    /**
     * @return array<string, string>
     */
    public function parseUseStatements(string $namespaceName): array
    {
        $currentNamespace = '';
        $statements = [];

        while ($token = $this->next()) {
            if ($currentNamespace === $namespaceName && $token[0] === T_USE) {
                $statements = array_merge($statements, $this->parseUseStatement());
                continue;
            }

            if ($token[0] !== T_NAMESPACE) {
                continue;
            }

            $currentNamespace = $this->parseNamespace();

            // Get fresh array for new namespace. This is to prevent the parser
            // to collect the use statements for a previous namespace with the
            // same name (this is the case if a namespace is defined twice).
            $statements = [];
        }

        return $statements;
    }

    /**
     * @return array<string, string>
     */
    private function parseUseStatement(): array
    {
        $groupRoot = '';
        $class = '';
        $alias = '';
        $statements = [];
        $explicitAlias = false;

        while ($token = $this->next()) {
            if (! $explicitAlias && $token[0] === T_STRING) {
                $class .= $token[1]; // @PHP8.0 remove concatenation
                $alias = $token[1];
            } elseif ($explicitAlias && $token[0] === T_STRING) {
                $alias = $token[1];
            } elseif (PHP_VERSION_ID >= 80000 // @PHP8.0 remove condition
                && ($token[0] === T_NAME_QUALIFIED || $token[0] === T_NAME_FULLY_QUALIFIED)
            ) {
                $class .= $token[1]; // @PHP8.0 remove concatenation
                $classSplit = explode('\\', $token[1]);
                $alias = $classSplit[count($classSplit) - 1];
            } elseif ($token[0] === T_NS_SEPARATOR) {
                $class .= '\\';
                $alias = '';
            } elseif ($token[0] === T_AS) {
                $explicitAlias = true;
                $alias = '';
            } elseif ($token === ',') {
                $statements[strtolower($alias)] = $groupRoot . $class;
                $class = '';
                $alias = '';
                $explicitAlias = false;
            } elseif ($token === ';') {
                if ($alias !== '') {
                    $statements[strtolower($alias)] = $groupRoot . $class;
                }
                break;
            } elseif ($token === '{') {
                $groupRoot = $class;
                $class = '';
            }
        }

        return $statements;
    }

    /**
     * Gets the next non whitespace and non comment token.
     *
     * @return array{0: int, 1: string}|string|null
     */
    private function next()
    {
        for ($i = $this->pointer; $i < $this->numTokens; $i++) {
            $this->pointer++;

            if ($this->tokens[$i][0] === T_WHITESPACE
                || $this->tokens[$i][0] === T_COMMENT
                || $this->tokens[$i][0] === T_DOC_COMMENT
            ) {
                continue;
            }

            return $this->tokens[$i];
        }

        return null;
    }

    private function parseNamespace(): string
    {
        $name = '';

        // @PHP8.0 remove `infection-ignore-all`
        // @infection-ignore-all
        while (($token = $this->next())
            // @PHP8.0 remove conditions
            && (
                (
                    PHP_VERSION_ID < 80000
                    && ($token[0] === T_NS_SEPARATOR || $token[0] === T_STRING)
                )
                || (
                    PHP_VERSION_ID >= 80000
                    && ($token[0] === T_NAME_QUALIFIED || $token[0] === T_NAME_FULLY_QUALIFIED)
                )
            )
        ) {
            $name .= $token[1]; // @PHP8.0 `return $token[1];` and `throw Error()` at the end of the method
        }

        return $name;
    }
}
