<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Lexer\Token;

use CuyZ\Valinor\Type\Parser\Lexer\TokenStream;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\IntegerValueType;

/** @internal */
final class IntegerValueToken implements TraversingToken
{
    private int $value;

    public function __construct(int $value)
    {
        $this->value = $value;
    }

    public function traverse(TokenStream $stream): Type
    {
        return new IntegerValueType($this->value);
    }
}
