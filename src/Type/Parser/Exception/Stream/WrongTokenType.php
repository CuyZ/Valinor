<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception\Stream;

use CuyZ\Valinor\Type\Parser\Lexer\Token\Token;
use CuyZ\Valinor\Type\Parser\Lexer\Token\TraversingToken;
use LogicException;

/** @internal */
final class WrongTokenType extends LogicException
{
    public function __construct(Token $token)
    {
        $class = $token::class;
        $wanted = TraversingToken::class;

        parent::__construct(
            "Wrong token type `$class`, it should be an instance of `$wanted`.",
            1618160414
        );
    }
}
