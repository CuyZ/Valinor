<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Lexer\Token;

use CuyZ\Valinor\Type\Parser\Lexer\TokenStream;
use CuyZ\Valinor\Type\Type;

final class TypeToken implements TraversingToken
{
    private Type $type;

    public function __construct(Type $type)
    {
        $this->type = $type;
    }

    public function traverse(TokenStream $stream): Type
    {
        return $this->type;
    }
}
