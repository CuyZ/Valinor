<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Lexer\Token;

use CuyZ\Valinor\Type\Parser\Exception\RightUnionTypeMissing;
use CuyZ\Valinor\Type\Parser\Lexer\TokenStream;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\UnionType;
use CuyZ\Valinor\Utility\IsSingleton;

/** @internal */
final class UnionToken implements LeftTraversingToken
{
    use IsSingleton;

    public function traverse(Type $type, TokenStream $stream): Type
    {
        if ($stream->done()) {
            throw new RightUnionTypeMissing($type);
        }

        return new UnionType($type, $stream->read());
    }

    public function symbol(): string
    {
        return '|';
    }
}
