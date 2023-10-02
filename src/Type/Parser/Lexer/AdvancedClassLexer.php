<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Lexer;

use CuyZ\Valinor\Type\Parser\Factory\TypeParserFactory;
use CuyZ\Valinor\Type\Parser\Lexer\Token\ClassNameToken;
use CuyZ\Valinor\Type\Parser\Lexer\Token\AdvancedClassNameToken;
use CuyZ\Valinor\Type\Parser\Lexer\Token\Token;

/** @internal */
final class AdvancedClassLexer implements TypeLexer
{
    public function __construct(
        private TypeLexer $delegate,
        private TypeParserFactory $typeParserFactory,
    ) {}

    public function tokenize(string $symbol): Token
    {
        $token = $this->delegate->tokenize($symbol);

        if ($token instanceof ClassNameToken) {
            return new AdvancedClassNameToken($token, $this->typeParserFactory);
        }

        return $token;
    }
}
