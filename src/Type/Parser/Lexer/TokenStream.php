<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Lexer;

use CuyZ\Valinor\Type\Parser\Exception\UnexpectedToken;
use CuyZ\Valinor\Type\Parser\Lexer\Token\LeftTraversingToken;
use CuyZ\Valinor\Type\Parser\Lexer\Token\Token;
use CuyZ\Valinor\Type\Parser\Lexer\Token\TraversingToken;
use CuyZ\Valinor\Type\Type;

use function assert;
use function count;

/** @internal */
final class TokenStream
{
    /** @var list<Token> */
    private array $tokens;

    private int $peek = -1;

    /**
     * This is used to prevent using the `read()` or `forward()` methods when
     * the stream is already done. Before calling these methods, the tokens
     * should *always* check if the stream is done and throw an appropriate
     * exception if that's the case.
     */
    private bool $hasCheckedIsNotDone = true;

    /**
     * @no-named-arguments
     */
    public function __construct(Token ...$tokens)
    {
        $this->tokens = $tokens;
    }

    /** @phpstan-impure */
    public function read(): Type
    {
        $token = $this->forward();

        if (! $token instanceof TraversingToken) {
            throw new UnexpectedToken($token->symbol());
        }

        $type = $token->traverse($this);

        while (! $this->done()) {
            $token = $this->next();

            if (! $token instanceof LeftTraversingToken) {
                break;
            }

            $this->forward();

            $type = $token->traverse($type, $this);
        }

        $this->hasCheckedIsNotDone = false;

        return $type;
    }

    public function next(): Token
    {
        assert($this->hasCheckedIsNotDone);

        return $this->tokens[$this->peek + 1];
    }

    /** @phpstan-impure */
    public function forward(): Token
    {
        assert($this->hasCheckedIsNotDone);

        $this->hasCheckedIsNotDone = false;

        return $this->tokens[++$this->peek];
    }

    /** @phpstan-impure */
    public function done(): bool
    {
        $this->hasCheckedIsNotDone = true;

        return $this->peek === count($this->tokens) - 1;
    }
}
