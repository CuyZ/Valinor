<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Lexer\Token;

use CuyZ\Valinor\Type\Parser\Lexer\TokenStream;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Utility\Reflection\Reflection;

/** @internal */
final class ObjectToken implements TraversingToken
{
    public function __construct(
        /** @var class-string */
        private string $className,
    ) {}

    public function traverse(TokenStream $stream): Type
    {
        return Reflection::enumExists($this->className)
            ? (new EnumNameToken($this->className))->traverse($stream)
            : (new ClassNameToken($this->className))->traverse($stream);
    }

    public function symbol(): string
    {
        return $this->className;
    }
}
