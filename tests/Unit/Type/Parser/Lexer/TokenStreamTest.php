<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Parser\Lexer;

use CuyZ\Valinor\Tests\Fake\Type\Parser\Lexer\Token\FakeToken;
use CuyZ\Valinor\Type\Parser\Exception\Stream\TryingToAccessOutboundToken;
use CuyZ\Valinor\Type\Parser\Exception\Stream\TryingToReadFinishedStream;
use CuyZ\Valinor\Type\Parser\Exception\Stream\WrongTokenType;
use CuyZ\Valinor\Type\Parser\Lexer\Token\TraversingToken;
use CuyZ\Valinor\Type\Parser\Lexer\TokenStream;
use PHPUnit\Framework\TestCase;

final class TokenStreamTest extends TestCase
{
    public function test_reading_finished_stream_throws_exception(): void
    {
        $this->expectException(TryingToReadFinishedStream::class);
        $this->expectExceptionCode(1_618_160_196);
        $this->expectExceptionMessage('Trying to read a finished stream.');

        (new TokenStream())->read();
    }

    public function test_invalid_token_encountered_throws_exception(): void
    {
        $this->expectException(WrongTokenType::class);
        $this->expectExceptionCode(1_618_160_414);
        $this->expectExceptionMessage('Wrong token type `' . FakeToken::class . '`, it should be an instance of `' . TraversingToken::class . '`.');

        (new TokenStream(new FakeToken()))->read();
    }

    public function test_getting_outbound_next_token_throws_exception(): void
    {
        $this->expectException(TryingToAccessOutboundToken::class);
        $this->expectExceptionCode(1_618_160_479);
        $this->expectExceptionMessage('Trying to access outbound token.');

        (new TokenStream())->next();
    }

    public function test_getting_outbound_forward_token_throws_exception(): void
    {
        $this->expectException(TryingToAccessOutboundToken::class);
        $this->expectExceptionCode(1_618_160_479);
        $this->expectExceptionMessage('Trying to access outbound token.');

        (new TokenStream())->forward();
    }
}
