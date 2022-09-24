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
    /** @var array<int, array<int, int|string>|string> */
    private array $tokens;

    private int $numTokens;

    private int $pointer = 0;

    public function __construct(string $contents)
    {
        $this->tokens = token_get_all($contents);

        /** @see https://github.com/doctrine/annotations/blob/4858ab786a6cb568149209a9112dad3808c8a4de/lib/Doctrine/Common/Annotations/TokenParser.php#L55-L61 */
        token_get_all("<?php\n/**\n *\n */"); // @phpstan-ignore-line

        $this->numTokens = count($this->tokens);
    }

    /**
     * Gets the next non whitespace and non comment token.
     * @return array<int, int|string>|string|null
     */
    public function next()
    {
        for ($i = $this->pointer; $i < $this->numTokens; $i++) {
            $this->pointer++;
            if (
                $this->tokens[$i][0] === T_WHITESPACE ||
                $this->tokens[$i][0] === T_COMMENT ||
                $this->tokens[$i][0] === T_DOC_COMMENT
            ) {
                continue;
            }

            return $this->tokens[$i];
        }

        return null;
    }

    /**
     * Parses a single use statement.
     *
     * @return array<string, string> A list with all found class names for a use statement.
     */
    public function parseUseStatement(): array
    {
        $groupRoot = '';
        $class = '';
        $alias = '';
        $statements = [];
        $explicitAlias = false;
        while (($token = $this->next())) {
            if (!$explicitAlias && $token[0] === T_STRING) {
                $class .= $token[1];
                $alias = $token[1];
            } elseif ($explicitAlias && $token[0] === T_STRING) {
                $alias = $token[1];
            } elseif (
                PHP_VERSION_ID >= 80000 &&
                ($token[0] === T_NAME_QUALIFIED || $token[0] === T_NAME_FULLY_QUALIFIED)
            ) {
                $class .= $token[1];

                $classSplit = explode('\\', (string)$token[1]);
                $alias = $classSplit[count($classSplit) - 1];
            } elseif ($token[0] === T_NS_SEPARATOR) {
                $class .= '\\';
                $alias = '';
            } elseif ($token[0] === T_AS) {
                $explicitAlias = true;
                $alias = '';
            } elseif ($token === ',') {
                $statements[strtolower((string)$alias)] = $groupRoot . $class;
                $class = '';
                $alias = '';
                $explicitAlias = false;
            } elseif ($token === ';') {
                $statements[strtolower((string)$alias)] = $groupRoot . $class;
                break;
            } elseif ($token === '{') {
                $groupRoot = $class;
                $class = '';
            } elseif ($token === '}') {
                continue;
            } else {
                break;
            }
        }

        return $statements;
    }

    /**
     * Gets all use statements.
     *
     * @param string $namespaceName The namespace name of the reflected class.
     *
     * @return array<string, string> A list with all found use statements.
     */
    public function parseUseStatements(string $namespaceName): array
    {
        $statements = [];
        while (($token = $this->next())) {
            if ($token[0] === T_USE) {
                $statements = array_merge($statements, $this->parseUseStatement());
                continue;
            }

            if ($token[0] !== T_NAMESPACE || $this->parseNamespace() !== $namespaceName) {
                continue;
            }

            // Get fresh array for new namespace. This is to prevent the parser to collect the use statements
            // for a previous namespace with the same name. This is the case if a namespace is defined twice
            // or if a namespace with the same name is commented out.
            $statements = [];
        }

        return $statements;
    }

    /**
     * Gets the namespace.
     *
     * @return string The found namespace.
     */
    public function parseNamespace(): string
    {
        $name = '';
        while (
            ($token = $this->next()) && ($token[0] === T_STRING || $token[0] === T_NS_SEPARATOR || (
                PHP_VERSION_ID >= 80000 &&
                    ($token[0] === T_NAME_QUALIFIED || $token[0] === T_NAME_FULLY_QUALIFIED)
            ))
        ) {
            $name .= $token[1];
        }

        return $name;
    }
}
