<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Lexer\Token;

use CuyZ\Valinor\Type\Parser\Exception\Iterable\SimpleArrayClosingBracketMissing;
use CuyZ\Valinor\Type\Parser\Lexer\TokenStream;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\ArrayType;
use CuyZ\Valinor\Utility\IsSingleton;

/** @internal */
final class OpeningSquareBracketToken implements LeftTraversingToken
{
    use IsSingleton;

    public function traverse(Type $type, TokenStream $stream): Type
    {
        if ($stream->done() || ! $stream->forward() instanceof ClosingSquareBracketToken) {
            throw new SimpleArrayClosingBracketMissing($type);
        }

        return ArrayType::simple($type);
    }

    public function symbol(): string
    {
        return '[';
    }
}
