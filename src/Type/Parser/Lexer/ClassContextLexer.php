<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Lexer;

use CuyZ\Valinor\Type\Parser\Lexer\Token\Token;
use CuyZ\Valinor\Utility\Reflection\Reflection;
use ReflectionClass;

/** @internal */
final class ClassContextLexer implements TypeLexer
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
        if ($symbol === 'self' || $symbol === 'static') {
            return $this->reflection->name;
        }

        return $symbol;
    }
}
