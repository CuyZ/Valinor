<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Lexer\Token;

use CuyZ\Valinor\Utility\IsSingleton;

/** @internal */
final class ClosingSquareBracketToken implements Token
{
    use IsSingleton;

    public function symbol(): string
    {
        return ']';
    }
}
