<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Lexer\Token;

use CuyZ\Valinor\Type\Parser\Lexer\TokenStream;
use CuyZ\Valinor\Type\Type;

/** @internal */
interface TraversingToken extends Token
{
    public function traverse(TokenStream $stream): Type;
}
