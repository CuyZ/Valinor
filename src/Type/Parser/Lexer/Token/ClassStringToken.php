<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Lexer\Token;

use CuyZ\Valinor\Type\ObjectType;
use CuyZ\Valinor\Type\Parser\Exception\Scalar\ClassStringClosingBracketMissing;
use CuyZ\Valinor\Type\Parser\Exception\Scalar\InvalidClassStringSubType;
use CuyZ\Valinor\Type\Parser\Lexer\TokenStream;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\ClassStringType;
use CuyZ\Valinor\Type\Types\UnionType;
use CuyZ\Valinor\Utility\IsSingleton;

/** @internal */
final class ClassStringToken implements TraversingToken
{
    use IsSingleton;

    public function traverse(TokenStream $stream): Type
    {
        if ($stream->done() || ! $stream->next() instanceof OpeningBracketToken) {
            return new ClassStringType();
        }

        $stream->forward();

        $type = $stream->read();

        if (! $type instanceof ObjectType && ! $type instanceof UnionType) {
            throw new InvalidClassStringSubType($type);
        }

        if ($stream->done() || ! $stream->forward() instanceof ClosingBracketToken) {
            throw new ClassStringClosingBracketMissing($type);
        }

        return new ClassStringType($type);
    }

    public function symbol(): string
    {
        return 'class-string';
    }
}
