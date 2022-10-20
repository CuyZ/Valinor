<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser;

use CuyZ\Valinor\Type\Parser\Lexer\TokenStream;
use CuyZ\Valinor\Type\Parser\Lexer\TypeLexer;
use CuyZ\Valinor\Type\Type;

use function array_filter;
use function array_map;
use function preg_split;
use function str_contains;

/** @internal */
final class LexingParser implements TypeParser
{
    public function __construct(private TypeLexer $lexer)
    {
    }

    public function parse(string $raw): Type
    {
        $symbols = $this->splitTokens($raw);
        $symbols = array_map('trim', $symbols);
        $symbols = array_filter($symbols, static fn ($value) => $value !== '');

        $tokens = array_map(
            fn (string $symbol) => $this->lexer->tokenize($symbol),
            $symbols
        );

        return (new TokenStream(...$tokens))->read();
    }

    /**
     * @return string[]
     */
    private function splitTokens(string $raw): array
    {
        if (str_contains($raw, "@anonymous\0")) {
            return $this->splitTokensContainingAnonymousClass($raw);
        }

        /** @phpstan-ignore-next-line */
        return preg_split('/([\s?|&<>,\[\]{}:\'"])/', $raw, -1, PREG_SPLIT_DELIM_CAPTURE);
    }

    /**
     * @return string[]
     */
    private function splitTokensContainingAnonymousClass(string $raw): array
    {
        /** @var string[] $splits */
        $splits = preg_split('/([a-zA-Z_\x7f-\xff][\\\\\w\x7f-\xff]*+@anonymous\x00.*?\.php(?:0x?|:\d++\$)[\da-fA-F]++)/', $raw, -1, PREG_SPLIT_DELIM_CAPTURE);
        $symbols = [];

        foreach ($splits as $symbol) {
            if (str_contains($symbol, "@anonymous\0")) {
                $symbols[] = $symbol;
            } else {
                $symbols = [...$symbols, ...$this->splitTokens($symbol)];
            }
        }

        return $symbols;
    }
}
