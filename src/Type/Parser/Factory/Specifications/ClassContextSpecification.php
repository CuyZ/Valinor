<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Factory\Specifications;

use CuyZ\Valinor\Type\Parser\Lexer\Token\ObjectToken;
use CuyZ\Valinor\Type\Parser\Lexer\Token\TraversingToken;

/** @internal */
final class ClassContextSpecification implements TypeParserSpecification
{
    public function __construct(
        /** @var class-string */
        private string $className,
    ) {}

    public function manipulateToken(TraversingToken $token): TraversingToken
    {
        if ($token->symbol() === 'self' || $token->symbol() === 'static') {
            return ObjectToken::from($this->className);
        }

        return $token;
    }
}
