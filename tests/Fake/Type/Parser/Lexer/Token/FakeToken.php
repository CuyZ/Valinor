<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fake\Type\Parser\Lexer\Token;

use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Type\Parser\Lexer\Token\TraversingToken;
use CuyZ\Valinor\Type\Parser\Lexer\TokenStream;
use CuyZ\Valinor\Type\Type;

final class FakeToken implements TraversingToken
{
    public function __construct(
        private string $symbol = 'fake-token',
    ) {}

    public function traverse(TokenStream $stream): Type
    {
        return new FakeType();
    }

    public function symbol(): string
    {
        return $this->symbol;
    }
}
