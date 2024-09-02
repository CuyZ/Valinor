<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Factory\Specifications;

use CuyZ\Valinor\Type\Parser\Lexer\Token\ObjectToken;
use CuyZ\Valinor\Type\Parser\Lexer\Token\TraversingToken;
use CuyZ\Valinor\Utility\Reflection\PhpParser;
use CuyZ\Valinor\Utility\Reflection\Reflection;
use ReflectionClass;
use ReflectionFunction;
use Reflector;

use function array_shift;
use function explode;
use function strtolower;

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

    private function resolveAlias(string $symbol): string
    {
        $alias = $symbol;

        $namespaceParts = explode('\\', $symbol);
        $firstPart = array_shift($namespaceParts);

        if ($firstPart) {
            $alias = strtolower($firstPart);
        }

        $aliases = PhpParser::parseUseStatements($this->reflection);

        if (! isset($aliases[$alias])) {
            return $symbol;
        }

        $fqcn = $aliases[$alias]['fqcn'];

        if ($namespaceParts === []) {
            return $fqcn;
        }

        if (! $aliases[$alias]['isExplicitAlias']) {
            return $symbol;
        }

        return $fqcn . '\\' . implode('\\', $namespaceParts);
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

        if (! $reflection->inNamespace()) {
            return $symbol;
        }

        $full = $reflection->getNamespaceName() . '\\' . $symbol;

        if (Reflection::classOrInterfaceExists($full)) {
            return $full;
        }

        return $symbol;
    }
}
