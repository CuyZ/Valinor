<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Lexer;

use CuyZ\Valinor\Type\Parser\Exception\Stream\TryingToAccessOutboundToken;
use CuyZ\Valinor\Type\Parser\Exception\Stream\TryingToReadFinishedStream;
use CuyZ\Valinor\Type\Parser\Exception\Stream\WrongTokenType;
use CuyZ\Valinor\Type\Parser\Lexer\Token\LeftTraversingToken;
use CuyZ\Valinor\Type\Parser\Lexer\Token\Token;
use CuyZ\Valinor\Type\Parser\Lexer\Token\TraversingToken;
use CuyZ\Valinor\Type\Type;

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

    public function read(): Type
    {
        if ($this->done()) {
            throw new TryingToReadFinishedStream();
        }

        $token = $this->forward();

        if (! $token instanceof TraversingToken) {
            throw new WrongTokenType($token);
        }

        $type = $token->traverse($this);

        while (! $this->done()) {
            $token = $this->next();

            if (! $token instanceof LeftTraversingToken) {
                /** @infection-ignore-all */
                break;
            }

            $this->forward();

            $type = $token->traverse($type, $this);
        }

        return $type;
    }

    public function next(): Token
    {
        $peek = $this->peek + 1;

        if (! isset($this->tokens[$peek])) {
            throw new TryingToAccessOutboundToken();
        }

        return $this->tokens[$peek];
    }

    public function forward(): Token
    {
        $this->peek++;

        if (! isset($this->tokens[$this->peek])) {
            throw new TryingToAccessOutboundToken();
        }

        return $this->tokens[$this->peek];
    }

    /** @phpstan-impure */
    public function done(): bool
    {
        return $this->peek === count($this->tokens) - 1;
    }
}
