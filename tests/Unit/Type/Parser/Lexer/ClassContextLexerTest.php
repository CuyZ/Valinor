<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Parser\Lexer;

use CuyZ\Valinor\Tests\Fake\Type\Parser\Lexer\FakeTypeLexer;
use CuyZ\Valinor\Tests\Fake\Type\Parser\Lexer\Token\FakeToken;
use CuyZ\Valinor\Type\Parser\Lexer\ClassContextLexer;
use PHPUnit\Framework\TestCase;
use stdClass;

final class ClassContextLexerTest extends TestCase
{
    private FakeTypeLexer $delegate;

    protected function setUp(): void
    {
        parent::setUp();

        $this->delegate = new FakeTypeLexer();
    }

    public function test_self_returns_class_name(): void
    {
        $lexer = new ClassContextLexer($this->delegate, stdClass::class);
        $symbol = 'self';
        $token = new FakeToken();

        $this->delegate->will(stdClass::class, $token);

        self::assertSame($token, $lexer->tokenize($symbol));
    }

    public function test_static_returns_class_name(): void
    {
        $lexer = new ClassContextLexer($this->delegate, stdClass::class);
        $symbol = 'static';
        $token = new FakeToken();

        $this->delegate->will(stdClass::class, $token);

        self::assertSame($token, $lexer->tokenize($symbol));
    }
}
