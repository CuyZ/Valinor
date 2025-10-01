<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Lexer;

use CuyZ\Valinor\Type\Parser\Lexer\Token\ArrayToken;
use CuyZ\Valinor\Type\Parser\Lexer\Token\CallableToken;
use CuyZ\Valinor\Type\Parser\Lexer\Token\ClassStringToken;
use CuyZ\Valinor\Type\Parser\Lexer\Token\ClosingBracketToken;
use CuyZ\Valinor\Type\Parser\Lexer\Token\ClosingCurlyBracketToken;
use CuyZ\Valinor\Type\Parser\Lexer\Token\ClosingParenthesisToken;
use CuyZ\Valinor\Type\Parser\Lexer\Token\ClosingSquareBracketToken;
use CuyZ\Valinor\Type\Parser\Lexer\Token\ColonToken;
use CuyZ\Valinor\Type\Parser\Lexer\Token\CommaToken;
use CuyZ\Valinor\Type\Parser\Lexer\Token\DoubleColonToken;
use CuyZ\Valinor\Type\Parser\Lexer\Token\FloatValueToken;
use CuyZ\Valinor\Type\Parser\Lexer\Token\IntegerToken;
use CuyZ\Valinor\Type\Parser\Lexer\Token\IntegerValueToken;
use CuyZ\Valinor\Type\Parser\Lexer\Token\IntersectionToken;
use CuyZ\Valinor\Type\Parser\Lexer\Token\ListToken;
use CuyZ\Valinor\Type\Parser\Lexer\Token\NullableToken;
use CuyZ\Valinor\Type\Parser\Lexer\Token\OpeningBracketToken;
use CuyZ\Valinor\Type\Parser\Lexer\Token\OpeningCurlyBracketToken;
use CuyZ\Valinor\Type\Parser\Lexer\Token\OpeningParenthesisToken;
use CuyZ\Valinor\Type\Parser\Lexer\Token\OpeningSquareBracketToken;
use CuyZ\Valinor\Type\Parser\Lexer\Token\StringValueToken;
use CuyZ\Valinor\Type\Parser\Lexer\Token\Token;
use CuyZ\Valinor\Type\Parser\Lexer\Token\TripleDotsToken;
use CuyZ\Valinor\Type\Parser\Lexer\Token\TypeToken;
use CuyZ\Valinor\Type\Parser\Lexer\Token\UnionToken;
use CuyZ\Valinor\Type\Parser\Lexer\Token\ValueOfToken;
use CuyZ\Valinor\Type\Types\ArrayKeyType;
use CuyZ\Valinor\Type\Types\BooleanValueType;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\NativeBooleanType;
use CuyZ\Valinor\Type\Types\NativeFloatType;
use CuyZ\Valinor\Type\Types\NativeStringType;
use CuyZ\Valinor\Type\Types\NegativeIntegerType;
use CuyZ\Valinor\Type\Types\NonEmptyStringType;
use CuyZ\Valinor\Type\Types\NonNegativeIntegerType;
use CuyZ\Valinor\Type\Types\NonPositiveIntegerType;
use CuyZ\Valinor\Type\Types\NullType;
use CuyZ\Valinor\Type\Types\NumericStringType;
use CuyZ\Valinor\Type\Types\PositiveIntegerType;
use CuyZ\Valinor\Type\Types\ScalarConcreteType;
use CuyZ\Valinor\Type\Types\UndefinedObjectType;

use function filter_var;
use function is_numeric;
use function str_starts_with;
use function strtolower;

/** @internal */
final class NativeLexer implements TypeLexer
{
    public function __construct(private TypeLexer $delegate) {}

    public function tokenize(string $symbol): Token
    {
        return match (strtolower($symbol)) {
            '|' => UnionToken::get(),
            '&' => IntersectionToken::get(),
            '(' => OpeningParenthesisToken::get(),
            ')' => ClosingParenthesisToken::get(),
            '<' => OpeningBracketToken::get(),
            '>' => ClosingBracketToken::get(),
            '[' => OpeningSquareBracketToken::get(),
            ']' => ClosingSquareBracketToken::get(),
            '{' => OpeningCurlyBracketToken::get(),
            '}' => ClosingCurlyBracketToken::get(),
            '::' => DoubleColonToken::get(),
            ':' => ColonToken::get(),
            '?' => NullableToken::get(),
            ',' => CommaToken::get(),
            '...' => TripleDotsToken::get(),

            'int', 'integer' => IntegerToken::get(),
            'array' => ArrayToken::array(),
            'non-empty-array' => ArrayToken::nonEmptyArray(),
            'iterable' => ArrayToken::iterable(),
            'list' => ListToken::list(),
            'non-empty-list' => ListToken::nonEmptyList(),
            'class-string' => ClassStringToken::get(),
            'callable' => CallableToken::get(),
            'value-of' => ValueOfToken::get(),

            'null' => new TypeToken(NullType::get()),
            'true' => new TypeToken(BooleanValueType::true()),
            'false' => new TypeToken(BooleanValueType::false()),
            'mixed' => new TypeToken(MixedType::get()),
            'float' => new TypeToken(NativeFloatType::get()),
            'positive-int' => new TypeToken(PositiveIntegerType::get()),
            'negative-int' => new TypeToken(NegativeIntegerType::get()),
            'non-positive-int' => new TypeToken(NonPositiveIntegerType::get()),
            'non-negative-int' => new TypeToken(NonNegativeIntegerType::get()),
            'string' => new TypeToken(NativeStringType::get()),
            'non-empty-string' => new TypeToken(NonEmptyStringType::get()),
            'numeric-string' => new TypeToken(NumericStringType::get()),
            'bool', 'boolean' => new TypeToken(NativeBooleanType::get()),
            'array-key' => new TypeToken(ArrayKeyType::default()),
            'object' => new TypeToken(UndefinedObjectType::get()),
            'scalar' => new TypeToken(ScalarConcreteType::get()),

            default => match (true) {
                str_starts_with($symbol, "'") || str_starts_with($symbol, '"') => new StringValueToken($symbol),
                filter_var($symbol, FILTER_VALIDATE_INT) !== false => new IntegerValueToken((int)$symbol),
                is_numeric($symbol) => new FloatValueToken((float)$symbol),
                default => $this->delegate->tokenize($symbol),
            },
        };
    }
}
