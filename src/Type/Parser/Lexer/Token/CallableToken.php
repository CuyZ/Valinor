<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Lexer\Token;

use CuyZ\Valinor\Type\Parser\Lexer\TokenStream;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\CallableType;
use CuyZ\Valinor\Utility\IsSingleton;

/** @internal */
final class CallableToken implements TraversingToken
{
    use IsSingleton;

    public function traverse(TokenStream $stream): Type
    {
        return CallableType::get();
    }

    public function symbol(): string
    {
        return 'callable';
    }
}
