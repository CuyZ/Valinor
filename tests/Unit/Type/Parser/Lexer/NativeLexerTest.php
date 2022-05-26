<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Parser\Lexer;

use CuyZ\Valinor\Tests\Fixture\Enum\PureEnum;
use CuyZ\Valinor\Type\Parser\Lexer\NativeLexer;
use CuyZ\Valinor\Type\Parser\Lexer\Token\ArrayToken;
use CuyZ\Valinor\Type\Parser\Lexer\Token\ClassNameToken;
use CuyZ\Valinor\Type\Parser\Lexer\Token\ClassStringToken;
use CuyZ\Valinor\Type\Parser\Lexer\Token\ClosingBracketToken;
use CuyZ\Valinor\Type\Parser\Lexer\Token\ClosingCurlyBracketToken;
use CuyZ\Valinor\Type\Parser\Lexer\Token\ClosingSquareBracketToken;
use CuyZ\Valinor\Type\Parser\Lexer\Token\ColonToken;
use CuyZ\Valinor\Type\Parser\Lexer\Token\CommaToken;
use CuyZ\Valinor\Type\Parser\Lexer\Token\EnumNameToken;
use CuyZ\Valinor\Type\Parser\Lexer\Token\FloatValueToken;
use CuyZ\Valinor\Type\Parser\Lexer\Token\IntegerToken;
use CuyZ\Valinor\Type\Parser\Lexer\Token\IntegerValueToken;
use CuyZ\Valinor\Type\Parser\Lexer\Token\IntersectionToken;
use CuyZ\Valinor\Type\Parser\Lexer\Token\IterableToken;
use CuyZ\Valinor\Type\Parser\Lexer\Token\ListToken;
use CuyZ\Valinor\Type\Parser\Lexer\Token\NativeToken;
use CuyZ\Valinor\Type\Parser\Lexer\Token\NullableToken;
use CuyZ\Valinor\Type\Parser\Lexer\Token\OpeningBracketToken;
use CuyZ\Valinor\Type\Parser\Lexer\Token\OpeningCurlyBracketToken;
use CuyZ\Valinor\Type\Parser\Lexer\Token\OpeningSquareBracketToken;
use CuyZ\Valinor\Type\Parser\Lexer\Token\StringValueToken;
use CuyZ\Valinor\Type\Parser\Lexer\Token\Token;
use CuyZ\Valinor\Type\Parser\Lexer\Token\UnionToken;
use CuyZ\Valinor\Type\Parser\Lexer\Token\UnknownSymbolToken;
use PHPUnit\Framework\TestCase;
use stdClass;

final class NativeLexerTest extends TestCase
{
    private NativeLexer $lexer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->lexer = new NativeLexer();
    }

    /**
     * @dataProvider tokenized_type_is_correct_data_provider
     *
     * @param class-string<Token> $tokenClassName
     */
    public function test_tokenized_type_is_correct(string $symbol, string $tokenClassName): void
    {
        $token = $this->lexer->tokenize($symbol);

        self::assertInstanceOf($tokenClassName, $token);
        self::assertSame($symbol, $token->symbol());
    }

    public function tokenized_type_is_correct_data_provider(): iterable
    {
        yield 'null' => [
            'symbol' => 'null',
            'token' => NativeToken::class,
        ];
        yield 'union' => [
            'symbol' => '|',
            'token' => UnionToken::class,
        ];
        yield 'intersection' => [
            'symbol' => '&',
            'token' => IntersectionToken::class,
        ];
        yield 'opening bracket' => [
            'symbol' => '<',
            'token' => OpeningBracketToken::class,
        ];
        yield 'closing bracket' => [
            'symbol' => '>',
            'token' => ClosingBracketToken::class,
        ];
        yield 'opening square bracket' => [
            'symbol' => '[',
            'token' => OpeningSquareBracketToken::class,
        ];
        yield 'closing square bracket' => [
            'symbol' => ']',
            'token' => ClosingSquareBracketToken::class,
        ];
        yield 'opening curly bracket' => [
            'symbol' => '{',
            'token' => OpeningCurlyBracketToken::class,
        ];
        yield 'closing curly bracket' => [
            'symbol' => '}',
            'token' => ClosingCurlyBracketToken::class,
        ];
        yield 'colon' => [
            'symbol' => ':',
            'token' => ColonToken::class,
        ];
        yield 'nullable' => [
            'symbol' => '?',
            'token' => NullableToken::class,
        ];
        yield 'comma' => [
            'symbol' => ',',
            'token' => CommaToken::class,
        ];
        yield 'int' => [
            'symbol' => 'int',
            'token' => IntegerToken::class,
        ];
        yield 'array' => [
            'symbol' => 'array',
            'token' => ArrayToken::class,
        ];
        yield 'non empty array' => [
            'symbol' => 'non-empty-array',
            'token' => ArrayToken::class,
        ];
        yield 'list' => [
            'symbol' => 'list',
            'token' => ListToken::class,
        ];
        yield 'non empty list' => [
            'symbol' => 'non-empty-list',
            'token' => ListToken::class,
        ];
        yield 'iterable' => [
            'symbol' => 'iterable',
            'token' => IterableToken::class,
        ];
        yield 'class-string' => [
            'symbol' => 'class-string',
            'token' => ClassStringToken::class,
        ];
        yield 'single quote string value' => [
            'symbol' => "'foo'",
            'token' => StringValueToken::class,
        ];
        yield 'double quote string value' => [
            'symbol' => '"foo"',
            'token' => StringValueToken::class,
        ];
        yield 'integer value' => [
            'symbol' => '1337',
            'token' => IntegerValueToken::class,
        ];
        yield 'float value' => [
            'symbol' => '1337.42',
            'token' => FloatValueToken::class,
        ];
        yield 'class' => [
            'symbol' => stdClass::class,
            'token' => ClassNameToken::class,
        ];
        yield 'unknown' => [
            'symbol' => 'unknown',
            'token' => UnknownSymbolToken::class,
        ];

        if (PHP_VERSION_ID >= 8_01_00) {
            yield 'enum' => [
                'symbol' => PureEnum::class,
                'token' => EnumNameToken::class,
            ];
        }
    }
}
