<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Lexer;

use CuyZ\Valinor\Type\Parser\Lexer\Token\LeftTraversingToken;
use CuyZ\Valinor\Type\Parser\Lexer\Token\Token;
use CuyZ\Valinor\Type\Parser\Lexer\Token\TraversingToken;
use CuyZ\Valinor\Type\Type;

use function assert;

/** @internal */
final class TokenStream
{
    /** @var Token[] */
    private array $tokens;

    private int $peek = -1;

    public function __construct(Token ...$tokens)
    {
        $this->tokens = $tokens;
    }

    /** @phpstan-impure */
    public function read(): Type
    {
        assert(! $this->done());

        $token = $this->forward();

        assert($token instanceof TraversingToken);

        $type = $token->traverse($this);

        while (! $this->done()) {
            $token = $this->next();

            if (! $token instanceof LeftTraversingToken) {
                break;
            }

            $this->forward();

            $type = $token->traverse($type, $this);
        }

        return $type;
    }

    public function next(): Token
    {
        return $this->tokens[$this->peek + 1];
    }

    /** @phpstan-impure */
    public function forward(): Token
    {
        return $this->tokens[++$this->peek];
    }

    /** @phpstan-impure */
    public function done(): bool
    {
        return $this->peek === count($this->tokens) - 1;
    }
}
