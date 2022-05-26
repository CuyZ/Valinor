<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Lexer\Token;

use CuyZ\Valinor\Type\Parser\Lexer\TokenStream;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\FloatValueType;

/** @internal */
final class FloatValueToken implements TraversingToken
{
    private float $value;

    public function __construct(float $value)
    {
        $this->value = $value;
    }

    public function traverse(TokenStream $stream): Type
    {
        return new FloatValueType($this->value);
    }

    public function symbol(): string
    {
        return (string)$this->value;
    }
}
