<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Lexer\Token;

use CuyZ\Valinor\Type\Parser\Lexer\TokenStream;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\ClassType;
use CuyZ\Valinor\Type\Types\InterfaceType;
use CuyZ\Valinor\Utility\Reflection\Reflection;

/** @internal */
final class ClassNameToken implements TraversingToken
{
    /** @var class-string */
    private string $className;

    /**
     * @param class-string $className
     */
    public function __construct(string $className)
    {
        $this->className = $className;
    }

    public function traverse(TokenStream $stream): Type
    {
        $reflection = Reflection::class($this->className);

        return $reflection->isInterface() || $reflection->isAbstract()
            ? new InterfaceType($this->className)
            : new ClassType($this->className);
    }

    /**
     * @return class-string
     */
    public function className(): string
    {
        return $this->className;
    }

    public function symbol(): string
    {
        return $this->className;
    }
}
