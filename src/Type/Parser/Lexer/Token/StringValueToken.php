<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Lexer\Token;

use CuyZ\Valinor\Type\Parser\Lexer\TokenStream;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\StringValueType;

/** @internal */
final class StringValueToken implements TraversingToken
{
    private StringValueType $type;

    private function __construct(StringValueType $type)
    {
        $this->type = $type;
    }

    public static function singleQuote(string $value): self
    {
        return new self(StringValueType::singleQuote($value));
    }

    public static function doubleQuote(string $value): self
    {
        return new self(StringValueType::doubleQuote($value));
    }

    public function traverse(TokenStream $stream): Type
    {
        return $this->type;
    }

    public function symbol(): string
    {
        return $this->type->toString();
    }
}
