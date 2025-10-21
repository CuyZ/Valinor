<?php

namespace CuyZ\Valinor\Type\Parser;

use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use CuyZ\Valinor\Type\Parser\Lexer\TokensExtractor;
use CuyZ\Valinor\Type\Parser\Lexer\TokenStream;
use CuyZ\Valinor\Type\Parser\Lexer\TypeLexer;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\UnresolvableType;

use function array_map;

/** @internal */
class LexingParser implements TypeParser
{
    public function __construct(private TypeLexer $lexer) {}

    public function parse(string $raw): Type
    {
        try {
            $tokens = array_map(
                fn (string $symbol) => $this->lexer->tokenize($symbol),
                (new TokensExtractor($raw))->filtered()
            );

            return (new TokenStream(...$tokens))->read();
        } catch (InvalidType $invalidType) {
            return new UnresolvableType($raw, $invalidType->getMessage());
        }
    }
}
