<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Lexer;

use CuyZ\Valinor\Type\Parser\Lexer\Token\ArrayToken;
use CuyZ\Valinor\Type\Parser\Lexer\Token\ClassNameToken;
use CuyZ\Valinor\Type\Parser\Lexer\Token\ClassStringToken;
use CuyZ\Valinor\Type\Parser\Lexer\Token\ClosingBracketToken;
use CuyZ\Valinor\Type\Parser\Lexer\Token\ClosingCurlyBracketToken;
use CuyZ\Valinor\Type\Parser\Lexer\Token\ClosingSquareBracketToken;
use CuyZ\Valinor\Type\Parser\Lexer\Token\ColonToken;
use CuyZ\Valinor\Type\Parser\Lexer\Token\CommaToken;
use CuyZ\Valinor\Type\Parser\Lexer\Token\EnumNameToken;
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
use UnitEnum;

use function class_exists;
use function enum_exists;
use function filter_var;
use function interface_exists;
use function str_ends_with;
use function str_starts_with;
use function strtolower;
use function substr;

final class NativeLexer implements TypeLexer
{
    public function tokenize(string $symbol): Token
    {
        if (NativeToken::accepts($symbol)) {
            return NativeToken::from($symbol);
        }

        switch (strtolower($symbol)) {
            case '|':
                return UnionToken::get();
            case '&':
                return IntersectionToken::get();
            case '<':
                return OpeningBracketToken::get();
            case '>':
                return ClosingBracketToken::get();
            case '[':
                return OpeningSquareBracketToken::get();
            case ']':
                return ClosingSquareBracketToken::get();
            case '{':
                return OpeningCurlyBracketToken::get();
            case '}':
                return ClosingCurlyBracketToken::get();
            case ':':
                return ColonToken::get();
            case '?':
                return NullableToken::get();
            case ',':
                return CommaToken::get();
            case 'array':
                return ArrayToken::array();
            case 'non-empty-array':
                return ArrayToken::nonEmptyArray();
            case 'list':
                return ListToken::list();
            case 'non-empty-list':
                return ListToken::nonEmptyList();
            case 'iterable':
                return IterableToken::get();
            case 'class-string':
                return ClassStringToken::get();
        }

        if (str_starts_with($symbol, "'") && str_ends_with($symbol, "'")) {
            return StringValueToken::singleQuote(substr($symbol, 1, -1));
        }

        if (str_starts_with($symbol, '"') && str_ends_with($symbol, '"')) {
            return StringValueToken::doubleQuote(substr($symbol, 1, -1));
        }

        if (filter_var($symbol, FILTER_VALIDATE_INT) !== false) {
            return new IntegerValueToken((int)$symbol);
        }

        /** @infection-ignore-all */
        if (PHP_VERSION_ID >= 8_01_00 && enum_exists($symbol)) {
            /** @var class-string<UnitEnum> $symbol */
            return new EnumNameToken($symbol);
        }

        if (class_exists($symbol) || interface_exists($symbol)) {
            return new ClassNameToken($symbol);
        }

        return new UnknownSymbolToken($symbol);
    }
}
