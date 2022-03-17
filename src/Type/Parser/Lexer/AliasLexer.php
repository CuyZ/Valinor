<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Lexer;

use CuyZ\Valinor\Type\Parser\Lexer\Token\Token;
use CuyZ\Valinor\Utility\Reflection\ClassAliasParser;
use ReflectionClass;

use ReflectionFunction;

use Reflector;

use function class_exists;
use function interface_exists;

/** @internal */
final class AliasLexer implements TypeLexer
{
    private TypeLexer $delegate;

    /** @var ReflectionClass<object>|ReflectionFunction */
    private Reflector $reflection;

    /**
     * @param ReflectionClass<object>|ReflectionFunction $reflection
     */
    public function __construct(TypeLexer $delegate, Reflector $reflection)
    {
        $this->delegate = $delegate;
        $this->reflection = $reflection;
    }

    public function tokenize(string $symbol): Token
    {
        $symbol = $this->resolve($symbol);

        return $this->delegate->tokenize($symbol);
    }

    private function resolve(string $symbol): string
    {
        $alias = ClassAliasParser::get()->resolveAlias($symbol, $this->reflection);

        if ($alias !== $symbol) {
            return $alias;
        }

        $namespaced = $this->resolveNamespaced($symbol, $this->reflection);

        if ($namespaced !== $symbol) {
            return $namespaced;
        }

        return $symbol;
    }

    /**
     * @param ReflectionClass<object>|ReflectionFunction $reflection
     */
    private function resolveNamespaced(string $symbol, Reflector $reflection): string
    {
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

        if (class_exists($full) || interface_exists($full)) {
            return $full;
        }

        return $symbol;
    }
}
