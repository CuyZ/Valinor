<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Lexer;

use CuyZ\Valinor\Type\Parser\Lexer\Token\Token;
use CuyZ\Valinor\Type\Parser\Lexer\Token\TypeToken;
use CuyZ\Valinor\Type\Type;

final class GenericAssignerLexer implements TypeLexer
{
    private TypeLexer $delegate;

    /** @var array<string, Type> */
    private array $generics;

    /**
     * @param array<string, Type> $generics
     */
    public function __construct(TypeLexer $delegate, array $generics)
    {
        $this->delegate = $delegate;
        $this->generics = $generics;
    }

    public function tokenize(string $symbol): Token
    {
        if (isset($this->generics[$symbol])) {
            return new TypeToken($this->generics[$symbol]);
        }

        return $this->delegate->tokenize($symbol);
    }
}
