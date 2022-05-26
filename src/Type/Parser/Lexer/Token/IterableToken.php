<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Lexer\Token;

use CuyZ\Valinor\Type\IntegerType;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\InvalidIterableKey;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\IterableClosingBracketMissing;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\IterableCommaMissing;
use CuyZ\Valinor\Type\Parser\Lexer\TokenStream;
use CuyZ\Valinor\Type\StringType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\ArrayKeyType;
use CuyZ\Valinor\Type\Types\IterableType;
use CuyZ\Valinor\Utility\IsSingleton;

/** @internal */
final class IterableToken implements TraversingToken
{
    use IsSingleton;

    public function traverse(TokenStream $stream): Type
    {
        if ($stream->done() || ! $stream->next() instanceof OpeningBracketToken) {
            return IterableType::native();
        }

        $stream->forward();
        $type = $stream->read();
        $token = $stream->forward();

        if ($token instanceof ClosingBracketToken) {
            return new IterableType(ArrayKeyType::default(), $type);
        }

        if (! $token instanceof CommaToken) {
            throw new IterableCommaMissing($type);
        }

        $subType = $stream->read();

        if ($type instanceof ArrayKeyType) {
            $iterableType = new IterableType($type, $subType);
        } elseif ($type instanceof IntegerType) {
            $iterableType = new IterableType(ArrayKeyType::integer(), $subType);
        } elseif ($type instanceof StringType) {
            $iterableType = new IterableType(ArrayKeyType::string(), $subType);
        } else {
            throw new InvalidIterableKey($type, $subType);
        }

        if ($stream->done() || ! $stream->forward() instanceof ClosingBracketToken) {
            throw new IterableClosingBracketMissing($type, $subType);
        }

        return $iterableType;
    }

    public function symbol(): string
    {
        return 'iterable';
    }
}
