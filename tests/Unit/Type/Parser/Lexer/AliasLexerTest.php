<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Parser\Lexer;

use CuyZ\Valinor\Tests\Fake\Type\Parser\Lexer\FakeTypeLexer;
use CuyZ\Valinor\Tests\Fake\Type\Parser\Lexer\Token\FakeToken;
use CuyZ\Valinor\Tests\Unit\Type\Parser\Lexer as PathAlias;
use CuyZ\Valinor\Type\Parser\Lexer\AliasLexer;
use DateTimeInterface as SecondClassAlias;
use ObjectWithNameMatchingRootNamespace\ObjectWithNameMatchingRootNamespace;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionFunction;
use stdClass;
use stdClass as FirstClassAlias;

final class AliasLexerTest extends TestCase
{
    private FakeTypeLexer $delegate;

    protected function setUp(): void
    {
        parent::setUp();

        $this->delegate = new FakeTypeLexer();
    }

    public function test_resolve_unsupported_type_in_class_returns_same_type(): void
    {
        $lexer = new AliasLexer($this->delegate, new ReflectionClass(stdClass::class));
        $symbol = 'SomeType';
        $token = new FakeToken();

        $this->delegate->will($symbol, $token);

        self::assertSame($token, $lexer->tokenize($symbol));
    }

    public function test_class_alias_are_checked_in_class(): void
    {
        $lexer = new AliasLexer($this->delegate, new ReflectionClass(ClassWithAlias::class));
        $symbol = 'FirstClassAlias';
        $token = new FakeToken();

        $this->delegate->will(stdClass::class, $token);

        self::assertSame($token, $lexer->tokenize($symbol));
    }

    public function test_class_alias_with_namespace_parts_is_checked_in_class(): void
    {
        $lexer = new AliasLexer($this->delegate, new ReflectionClass(ClassWithAlias::class));
        $symbol = 'PathAlias\\AliasLexerTest';
        $token = new FakeToken();

        $this->delegate->will(self::class, $token);

        self::assertSame($token, $lexer->tokenize($symbol));
    }

    public function test_same_namespace_classes_are_checked_in_class(): void
    {
        $lexer = new AliasLexer($this->delegate, new ReflectionClass(ClassWithAlias::class));
        $symbol = 'ClassWithAlias';
        $token = new FakeToken();

        $this->delegate->will(ClassWithAlias::class, $token);

        self::assertSame($token, $lexer->tokenize($symbol));
    }

    public function test_object_with_same_name_as_root_namespace_are_checked_in_class(): void
    {
        $lexer = new AliasLexer($this->delegate, new ReflectionClass(ClassWithAlias::class));
        $symbol = ObjectWithNameMatchingRootNamespace::class;
        $token = new FakeToken();

        $this->delegate->will(ObjectWithNameMatchingRootNamespace::class, $token);

        self::assertSame($token, $lexer->tokenize($symbol));
    }

    public function test_resolve_unsupported_type_in_function_returns_same_type(): void
    {
        $function = fn () => 42;

        $lexer = new AliasLexer($this->delegate, new ReflectionFunction($function));
        $symbol = 'SomeType';
        $token = new FakeToken();

        $this->delegate->will($symbol, $token);

        self::assertSame($token, $lexer->tokenize($symbol));
    }

    public function test_class_alias_are_checked_in_function(): void
    {
        $function = fn () => 42;

        $lexer = new AliasLexer($this->delegate, new ReflectionFunction($function));
        $symbol = 'FirstClassAlias';
        $token = new FakeToken();

        $this->delegate->will(stdClass::class, $token);

        self::assertSame($token, $lexer->tokenize($symbol));
    }

    public function test_class_alias_with_namespace_parts_is_checked_in_function(): void
    {
        $function = fn () => 42;

        $lexer = new AliasLexer($this->delegate, new ReflectionFunction($function));
        $symbol = 'PathAlias\\AliasLexerTest';
        $token = new FakeToken();

        $this->delegate->will(self::class, $token);

        self::assertSame($token, $lexer->tokenize($symbol));
    }

    public function test_same_namespace_classes_are_checked_in_function(): void
    {
        $function = fn () => 42;

        $lexer = new AliasLexer($this->delegate, new ReflectionFunction($function));
        $symbol = 'ClassWithAlias';
        $token = new FakeToken();

        $this->delegate->will(ClassWithAlias::class, $token);

        self::assertSame($token, $lexer->tokenize($symbol));
    }

    public function test_object_with_same_name_as_root_namespace_are_checked_in_function(): void
    {
        $function = fn () => 42;

        $lexer = new AliasLexer($this->delegate, new ReflectionFunction($function));
        $symbol = ObjectWithNameMatchingRootNamespace::class;
        $token = new FakeToken();

        $this->delegate->will(ObjectWithNameMatchingRootNamespace::class, $token);

        self::assertSame($token, $lexer->tokenize($symbol));
    }
}

final class ClassWithAlias
{
    public FirstClassAlias $propertyWithFirstAliasType;

    public SecondClassAlias $propertyWithSecondAliasType;

    public PathAlias\AliasLexerTest $propertyWithPathAliasType;
}
