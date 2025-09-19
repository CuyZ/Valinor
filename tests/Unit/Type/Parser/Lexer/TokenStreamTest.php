<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Parser\Lexer;

use AssertionError;
use CuyZ\Valinor\Tests\Fake\Type\Parser\Lexer\Token\FakeToken;
use CuyZ\Valinor\Type\Parser\Lexer\TokenStream;
use PHPUnit\Framework\TestCase;

final class TokenStreamTest extends TestCase
{
    public function test_read_when_not_checking_if_stream_is_done_is_asserted(): void
    {
        $this->expectException(AssertionError::class);

        $stream = new TokenStream(new FakeToken());

        $stream->read();
        $stream->read();
    }

    public function test_forward_when_not_checking_if_stream_is_done_is_asserted(): void
    {
        $this->expectException(AssertionError::class);

        $stream = new TokenStream(new FakeToken());

        $stream->forward();
        $stream->forward();
    }
}
