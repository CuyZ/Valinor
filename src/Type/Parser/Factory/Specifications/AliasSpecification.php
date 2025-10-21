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
use function implode;
use function in_array;
use function strtolower;

/** @internal */
final class AliasSpecification implements TypeParserSpecification
{
    public function __construct(
        /** @var ReflectionClass<covariant object>|ReflectionFunction */
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
            return ObjectToken::from($alias);
        }

        $namespaced = $this->resolveNamespaced($symbol);

        if ($namespaced !== $symbol) {
            /** @var class-string $namespaced */
            return ObjectToken::from($namespaced);
        }

        return $token;
    }

    private function resolveAlias(string $symbol): string
    {
        $aliases = PhpParser::parseUseStatements($this->reflection);

        if (in_array($symbol, $aliases, true)) {
            return $symbol;
        }

        $namespaceParts = explode('\\', $symbol);

        $alias = strtolower(array_shift($namespaceParts));

        if (! isset($aliases[$alias])) {
            return $symbol;
        }

        if ($namespaceParts === []) {
            return $aliases[$alias];
        }

        return $aliases[$alias] . '\\' . implode('\\', $namespaceParts);
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

        if ($reflection->inNamespace()) {
            $namespace = $reflection->getNamespaceName();
        } elseif ($reflection instanceof ReflectionFunction) {
            $namespace = PhpParser::parseNamespace($reflection);
        }

        if (! isset($namespace)) {
            return $symbol;
        }

        $full = "$namespace\\$symbol";

        if (Reflection::classOrInterfaceExists($full)) {
            return $full;
        }

        return $symbol;
    }
}
