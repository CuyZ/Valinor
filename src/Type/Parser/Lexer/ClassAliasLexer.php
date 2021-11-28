<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Lexer;

use CuyZ\Valinor\Type\Parser\Lexer\Token\Token;
use CuyZ\Valinor\Utility\Reflection\ClassAliasParser;
use CuyZ\Valinor\Utility\Reflection\Reflection;
use ReflectionClass;

use function class_exists;
use function interface_exists;

final class ClassAliasLexer implements TypeLexer
{
    private TypeLexer $delegate;

    /** @var ReflectionClass<object> */
    private ReflectionClass $reflection;

    /**
     * @param class-string $className
     */
    public function __construct(TypeLexer $delegate, string $className)
    {
        $this->delegate = $delegate;
        $this->reflection = Reflection::class($className);
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
     * @param ReflectionClass<object> $reflection
     */
    private function resolveNamespaced(string $symbol, ReflectionClass $reflection): string
    {
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
