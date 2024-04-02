<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Factory\Specifications;

use CuyZ\Valinor\Type\Parser\Factory\TypeParserFactory;
use CuyZ\Valinor\Type\Parser\Lexer\Token\ObjectToken;
use CuyZ\Valinor\Type\Parser\Lexer\Token\TraversingToken;
use CuyZ\Valinor\Type\Parser\TypeParser;
use CuyZ\Valinor\Utility\Reflection\PhpParser;
use CuyZ\Valinor\Utility\Reflection\Reflection;
use ReflectionClass;
use ReflectionFunction;
use Reflector;

/** @internal */
final class AliasSpecification implements TypeParserSpecification
{
    public function __construct(
        /** @var ReflectionClass<object>|ReflectionFunction */
        private Reflector $reflection,
    ) {}

    public function manipulateToken(TraversingToken $token): TraversingToken
    {
        $symbol = $token->symbol();

        // Matches the case where a class extends a class with the same name but
        // in a different namespace.
        if ($symbol === $this->reflection->getShortName() && Reflection::classOrInterfaceExists($symbol)) {
            return $token;
        }

        $alias = $this->resolveAlias($symbol);

        if (strtolower($alias) !== strtolower($symbol)) {
            /** @var class-string $alias */
            return new ObjectToken($alias);
        }

        $namespaced = $this->resolveNamespaced($symbol);

        if ($namespaced !== $symbol) {
            /** @var class-string $namespaced */
            return new ObjectToken($namespaced);
        }

        return $token;
    }

    public function manipulateParser(TypeParser $parser, TypeParserFactory $typeParserFactory): TypeParser
    {
        return $parser;
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

        if ($aliases[$alias] === $symbol) {
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
            $classReflection = $reflection->getClosureScopeClass();

            if ($classReflection && $classReflection->getFileName() === $reflection->getFileName()) {
                $reflection = $classReflection;
            }
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
