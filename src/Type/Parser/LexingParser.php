<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser;

use CuyZ\Valinor\Type\Parser\Exception\ImpossibleParsing;
use CuyZ\Valinor\Type\Parser\Lexer\TokenStream;
use CuyZ\Valinor\Type\Parser\Lexer\TypeLexer;
use CuyZ\Valinor\Type\Type;

use function array_filter;
use function array_map;
use function preg_split;
use function str_starts_with;

final class LexingParser implements TypeParser
{
    private TypeLexer $lexer;

    public function __construct(TypeLexer $lexer)
    {
        $this->lexer = $lexer;
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

        $stream = new TokenStream(...$tokens);

        $type = $stream->read();

        if (! $stream->done()) {
            throw new ImpossibleParsing($raw);
        }

        return $type;
    }

    /**
     * @return string[]
     */
    private function splitTokens(string $raw): array
    {
        if (str_contains($raw, "@anonymous\0")) {
            return $this->splitTokensContainingAnonymousClass($raw);
        }

        if (str_contains($raw, "'")) {
            return $this->splitQuotes("'", $raw);
        }

        if (str_contains($raw, '"')) {
            return $this->splitQuotes('"', $raw);
        }

        /** @phpstan-ignore-next-line */
        return preg_split('/([\s?|&<>,\[\]{}:])/', $raw, -1, PREG_SPLIT_DELIM_CAPTURE);
    }

    /**
     * @return string[]
     */
    private function splitQuotes(string $quote, string $raw): array
    {
        /** @var string[] $splits */
        $splits = preg_split("/({$quote}[^$quote]+$quote)/", $raw, -1, PREG_SPLIT_DELIM_CAPTURE);
        $symbols = [];

        foreach ($splits as $symbol) {
            if (str_starts_with($symbol, $quote)) {
                $symbols[] = $symbol;
            } else {
                $symbols = [...$symbols, ...$this->splitTokens($symbol)];
            }
        }

        return $symbols;
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
