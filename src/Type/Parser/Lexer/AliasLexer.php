<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Lexer;

use CuyZ\Valinor\Type\Parser\Lexer\Token\Token;
use CuyZ\Valinor\Utility\Reflection\PhpParser;
use CuyZ\Valinor\Utility\Reflection\Reflection;
use ReflectionClass;
use ReflectionFunction;
use Reflector;

use function strtolower;

/** @internal */
final class AliasLexer implements TypeLexer
{
    public function __construct(
        private TypeLexer $delegate,
        /** @var ReflectionClass<object>|ReflectionFunction */
        private Reflector $reflection
    ) {
    }

    public function tokenize(string $symbol): Token
    {
        $symbol = $this->resolve($symbol);

        return $this->delegate->tokenize($symbol);
    }

    private function resolve(string $symbol): string
    {
        $alias = $this->resolveAlias($symbol);

        if (strtolower($alias) !== strtolower($symbol)) {
            return $alias;
        }

        $namespaced = $this->resolveNamespaced($symbol);

        if ($namespaced !== $symbol) {
            return $namespaced;
        }

        return $symbol;
    }

    private function resolveAlias(string $symbol): string
    {
        $alias = $symbol;

        $namespaceParts = explode('\\', $symbol);
        $lastPart = array_shift($namespaceParts);

        if ($lastPart) {
            $alias = strtolower($lastPart);
        }

        $aliases = PhpParser::parseUseStatements($this->reflection);

        if (! isset($aliases[$alias])) {
            return $symbol;
        }

        $full = $aliases[$alias];

        if (! empty($namespaceParts)) {
            $full .= '\\' . implode('\\', $namespaceParts);
        }

        return $full;
    }

    private function resolveNamespaced(string $symbol): string
    {
        $reflection = $this->reflection;

        if ($reflection instanceof ReflectionFunction) {
            $reflection = $reflection->getClosureScopeClass();
        }

        if (! $reflection) {
            return $symbol;
        }

        $namespace = $reflection->getNamespaceName();

        if (! $namespace) {
            return $symbol;
        }

        $full = $namespace . '\\' . $symbol;

        if (Reflection::classOrInterfaceExists($full)) {
            return $full;
        }

        return $symbol;
    }
}
