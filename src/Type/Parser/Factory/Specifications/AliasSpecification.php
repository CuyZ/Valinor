<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Factory\Specifications;

use CuyZ\Valinor\Type\Parser\Lexer\AliasLexer;
use CuyZ\Valinor\Type\Parser\Lexer\TypeLexer;
use ReflectionClass;
use ReflectionFunction;
use Reflector;

/** @internal */
final class AliasSpecification implements TypeParserSpecification
{
    public function __construct(
        /** @var ReflectionClass<object>|ReflectionFunction */
        private Reflector $reflection
    ) {
    }

    public function transform(TypeLexer $lexer): TypeLexer
    {
        return new AliasLexer($lexer, $this->reflection);
    }
}
