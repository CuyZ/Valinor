<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Lexer\Token;

use CuyZ\Valinor\Type\Parser\Lexer\TokenStream;
use CuyZ\Valinor\Type\Type;

/** @internal */
final class TypeToken implements TraversingToken
{
    private Type $type;

    private string $symbol;

    public function __construct(Type $type)
    {
        $this->type = $type;
        $this->symbol = $type->toString();
    }

    public function traverse(TokenStream $stream): Type
    {
        return $this->type;
    }

    public function symbol(): string
    {
        return $this->symbol;
    }
}
