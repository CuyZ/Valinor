<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Lexer;

use CuyZ\Valinor\Type\Parser\Lexer\Token\ClassNameToken;
use CuyZ\Valinor\Type\Parser\Lexer\Token\EnumNameToken;
use CuyZ\Valinor\Type\Parser\Lexer\Token\Token;
use CuyZ\Valinor\Type\Parser\Lexer\Token\UnknownSymbolToken;
use CuyZ\Valinor\Utility\Reflection\Reflection;
use UnitEnum;

/** @internal */
final class ObjectLexer implements TypeLexer
{
    public function tokenize(string $symbol): Token
    {
        if (Reflection::enumExists($symbol)) {
            /** @var class-string<UnitEnum> $symbol */
            return new EnumNameToken($symbol);
        }

        if (Reflection::classOrInterfaceExists($symbol)) {
            /** @var class-string $symbol */
            return new ClassNameToken($symbol);
        }

        return new UnknownSymbolToken($symbol);
    }
}
