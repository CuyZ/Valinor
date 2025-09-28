<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Lexer\Token;

use CuyZ\Valinor\Type\Parser\Factory\Specifications\TypeParserSpecification;
use CuyZ\Valinor\Type\Parser\Lexer\TokenStream;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\UnresolvableType;

/** @internal */
final class VacantToken implements TraversingToken
{
    public function __construct(
        private string $symbol,
        /** @var array<TypeParserSpecification> */
        private array $specifications,
    ) {}

    public function traverse(TokenStream $stream): Type
    {
        $token = $this;

        foreach ($this->specifications as $specification) {
            $token = $specification->manipulateToken($token);
        }

        if ($token !== $this) {
            return $token->traverse($stream);
        }

        return new UnresolvableType($this->symbol, "Cannot parse unknown symbol `$this->symbol`.");
    }

    public function symbol(): string
    {
        return $this->symbol;
    }
}
