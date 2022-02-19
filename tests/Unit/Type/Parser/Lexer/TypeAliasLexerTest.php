<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Parser\Lexer;

use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Tests\Fake\Type\Parser\Lexer\FakeTypeLexer;
use CuyZ\Valinor\Tests\Fake\Type\Parser\Lexer\Token\FakeToken;
use CuyZ\Valinor\Type\Parser\Lexer\TypeAliasLexer;
use CuyZ\Valinor\Type\Parser\Lexer\Token\TypeToken;
use CuyZ\Valinor\Type\Parser\Lexer\TokenStream;
use PHPUnit\Framework\TestCase;

final class TypeAliasLexerTest extends TestCase
{
    private FakeTypeLexer $delegate;

    protected function setUp(): void
    {
        parent::setUp();

        $this->delegate = new FakeTypeLexer();
    }

    public function test_non_generic_symbol_is_handled_by_delegate(): void
    {
        $symbol = 'foo';
        $token = new FakeToken();

        $this->delegate->will($symbol, $token);

        $lexer = new TypeAliasLexer($this->delegate, ['TemplateA' => new FakeType()]);

        self::assertSame($token, $lexer->tokenize($symbol));
    }

    public function test_symbol_is_generic_is_returned(): void
    {
        $type = new FakeType();

        $lexer = new TypeAliasLexer($this->delegate, ['Template' => $type]);

        $result = $lexer->tokenize('Template');

        self::assertInstanceOf(TypeToken::class, $result);
        self::assertSame($type, $result->traverse(new TokenStream()));
    }
}
