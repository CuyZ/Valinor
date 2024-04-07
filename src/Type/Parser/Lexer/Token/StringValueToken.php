<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Lexer\Token;

use CuyZ\Valinor\Type\Parser\Exception\MissingClosingQuoteChar;
use CuyZ\Valinor\Type\Parser\Lexer\TokenStream;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\StringValueType;

/** @internal */
final class StringValueToken implements TraversingToken
{
    public function __construct(private string $value) {}

    public function traverse(TokenStream $stream): Type
    {
        $quoteType = $this->value[0];

        if ($this->value[-1] !== $quoteType) {
            throw new MissingClosingQuoteChar($this->value);
        }

        return StringValueType::from($this->value);
    }

    public function symbol(): string
    {
        return $this->value;
    }
}
