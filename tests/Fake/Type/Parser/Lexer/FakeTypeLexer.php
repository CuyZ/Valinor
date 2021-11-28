<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fake\Type\Parser\Lexer;

use CuyZ\Valinor\Tests\Fake\Type\Parser\Lexer\Token\FakeToken;
use CuyZ\Valinor\Type\Parser\Lexer\Token\Token;
use CuyZ\Valinor\Type\Parser\Lexer\TypeLexer;

final class FakeTypeLexer implements TypeLexer
{
    /** @var array<string, Token> */
    private array $tokens = [];

    public function tokenize(string $symbol): Token
    {
        return $this->tokens[$symbol] ??= new FakeToken();
    }

    public function will(string $symbol, Token $token): void
    {
        $this->tokens[$symbol] = $token;
    }
}
