<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Lexer\Token;

use CuyZ\Valinor\Type\Parser\Exception\UnknownSymbol;
use CuyZ\Valinor\Type\Parser\Lexer\TokenStream;
use CuyZ\Valinor\Type\Type;

final class UnknownSymbolToken implements TraversingToken
{
    private string $symbol;

    public function __construct(string $symbol)
    {
        $this->symbol = $symbol;
    }

    public function traverse(TokenStream $stream): Type
    {
        throw new UnknownSymbol($this->symbol);
    }

    public function symbol(): string
    {
        return $this->symbol;
    }
}
