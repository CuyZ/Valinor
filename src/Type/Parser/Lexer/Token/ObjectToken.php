<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Lexer\Token;

use CuyZ\Valinor\Type\Parser\Lexer\TokenStream;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Utility\Reflection\Reflection;

/** @internal */
final readonly class ObjectToken implements TraversingToken
{
    public function __construct(
        public TraversingToken $subToken,
    ) {}

    /**
     * @param class-string $className
     */
    public static function from(string $className): self
    {
        return Reflection::enumExists($className)
            ? new self(new EnumNameToken($className))
            : new self(new ClassNameToken($className));
    }

    public function traverse(TokenStream $stream): Type
    {
        return $this->subToken->traverse($stream);
    }

    public function symbol(): string
    {
        return $this->subToken->symbol();
    }
}
