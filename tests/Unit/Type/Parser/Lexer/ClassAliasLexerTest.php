<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Parser\Lexer;

use CuyZ\Valinor\Tests\Fake\Type\Parser\Lexer\FakeTypeLexer;
use CuyZ\Valinor\Tests\Fake\Type\Parser\Lexer\Token\FakeToken;
use CuyZ\Valinor\Tests\Unit\Type\Parser\Lexer as PathAlias;
use CuyZ\Valinor\Type\Parser\Lexer\ClassAliasLexer;
use DateTimeInterface as SecondClassAlias;
use PHPUnit\Framework\TestCase;
use stdClass;
use stdClass as FirstClassAlias;

final class ClassAliasLexerTest extends TestCase
{
    private FakeTypeLexer $delegate;

    protected function setUp(): void
    {
        parent::setUp();

        $this->delegate = new FakeTypeLexer();
    }

    public function test_resolve_unsupported_type_returns_same_type(): void
    {
        $lexer = new ClassAliasLexer($this->delegate, stdClass::class);
        $symbol = 'SomeType';
        $token = new FakeToken();

        $this->delegate->will($symbol, $token);

        self::assertSame($token, $lexer->tokenize($symbol));
    }

    public function test_class_alias_are_checked(): void
    {
        $lexer = new ClassAliasLexer($this->delegate, ClassWithAlias::class);
        $symbol = 'FirstClassAlias';
        $token = new FakeToken();

        $this->delegate->will(stdClass::class, $token);

        self::assertSame($token, $lexer->tokenize($symbol));
    }

    public function test_class_alias_with_namespace_parts_is_checked(): void
    {
        $lexer = new ClassAliasLexer($this->delegate, ClassWithAlias::class);
        $symbol = 'PathAlias\\ClassAliasLexerTest';
        $token = new FakeToken();

        $this->delegate->will(self::class, $token);

        self::assertSame($token, $lexer->tokenize($symbol));
    }

    public function test_same_namespace_classes_are_checked(): void
    {
        $lexer = new ClassAliasLexer($this->delegate, ClassWithAlias::class);
        $symbol = 'ClassWithAlias';
        $token = new FakeToken();

        $this->delegate->will(ClassWithAlias::class, $token);

        self::assertSame($token, $lexer->tokenize($symbol));
    }
}

final class ClassWithAlias
{
    public FirstClassAlias $propertyWithFirstAliasType;

    public SecondClassAlias $propertyWithSecondAliasType;

    public PathAlias\ClassAliasLexerTest $propertyWithPathAliasType;
}
