<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Utility\Reflection;

use LogicException;
use PhpToken;

use function array_pop;
use function count;
use function end;
use function explode;
use function strtolower;

/**
 * @internal
 *
 * Imported from `doctrine/annotations`:
 * @link https://github.com/doctrine/annotations/blob/de990c9a69782a8b15fa8c9248de0ef4d82ed701/lib/Doctrine/Common/Annotations/TokenParser.php
 */
final class TokenParser
{
    /** @var array<PhpToken> */
    private array $tokens;

    private int $numTokens;

    private int $pointer = 0;

    public function __construct(string $content)
    {
        $this->tokens = PhpToken::tokenize($content);
        $this->numTokens = count($this->tokens);
    }

    public function getNamespace(): string
    {
        $currentNamespace = '';

        while ($token = $this->next()) {
            if ($token->is(T_NAMESPACE)) {
                $currentNamespace = $this->parseNamespace();
            }
        }

        return $currentNamespace;
    }

    /**
     * @return array<string, string>
     */
    public function parseUseStatements(string $namespaceName): array
    {
        $this->pointer = 0;
        $currentNamespace = '';
        $statements = [];

        /** @var list<bool> $blocks */
        $blocks = [];
        $classLikeDeclaration = false;
        $previous = null;

        while ($token = $this->next()) {
            if ($token->is([T_CLASS, T_INTERFACE, T_TRAIT, T_ENUM])) {
                $classLikeDeclaration = ! ($previous?->is(T_DOUBLE_COLON) ?? false);
            } elseif ($token->is('{') || $token->text === '${') {
                $blocks[] = $classLikeDeclaration;
                $classLikeDeclaration = false;
            } elseif ($token->is('}')) {
                array_pop($blocks);
            } elseif ($token->is(T_USE)) {
                // Not a trait import nor a closure capture
                $isImport = $currentNamespace === $namespaceName
                    && end($blocks) !== true
                    && ! ($this->peek()?->is('(') ?? false);

                if ($isImport) {
                    $statements = [...$statements, ...$this->parseUseStatement()];
                }
            } elseif ($token->is(T_NAMESPACE)) {
                $currentNamespace = $this->parseNamespace();

                // Get fresh array for new namespace. This is to prevent the parser
                // to collect the use statements for a previous namespace with the
                // same name (this is the case if a namespace is defined twice).
                $statements = [];
            }

            $previous = $token;
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
            $name = (string)$token;

            if (! $explicitAlias && $token->is(T_STRING)) {
                $class = $alias = $name;
            } elseif ($explicitAlias && $token->is(T_STRING)) {
                $alias = $name;
            } elseif ($token->is([T_NAME_QUALIFIED, T_NAME_FULLY_QUALIFIED])) {
                $class = $name;
                $classSplit = explode('\\', $name);
                $alias = $classSplit[count($classSplit) - 1];
            } elseif ($token->is(T_NS_SEPARATOR)) {
                $class .= '\\';
                $alias = '';
            } elseif ($token->is(T_AS)) {
                $explicitAlias = true;
                $alias = '';
            } elseif ($name === ',') {
                $statements[strtolower($alias)] = $groupRoot . $class;
                $class = $alias = '';
                $explicitAlias = false;
            } elseif ($name === ';') {
                if ($alias !== '') {
                    $statements[strtolower($alias)] = $groupRoot . $class;
                }
                break;
            } elseif ($name === '{') {
                $groupRoot = $class;
                $class = '';
            }
        }

        return $statements;
    }

    private function next(): ?PhpToken
    {
        for ($i = $this->pointer; $i < $this->numTokens; $i++) {
            $this->pointer++;

            if (! $this->tokens[$i]->isIgnorable()) {
                return $this->tokens[$i];
            }
        }

        return null;
    }

    private function peek(): ?PhpToken
    {
        for ($i = $this->pointer; $i < $this->numTokens; $i++) {
            if (! $this->tokens[$i]->isIgnorable()) {
                return $this->tokens[$i];
            }
        }

        return null;
    }

    private function parseNamespace(): string
    {
        while ($token = $this->next()) {
            if ($token->is([T_NAME_QUALIFIED, T_NAME_FULLY_QUALIFIED, T_STRING])) {
                return (string)$token;
            } elseif ($token->is('{')) {
                return "";
            }
        }

        /** @infection-ignore-all */
        // @codeCoverageIgnoreStart
        throw new LogicException('Namespace not found.');
        // @codeCoverageIgnoreEnd
    }
}
