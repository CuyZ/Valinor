<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Factory\Specifications;

use CuyZ\Valinor\Type\Parser\Lexer\TypeAliasLexer;
use CuyZ\Valinor\Type\Parser\Lexer\TypeLexer;
use CuyZ\Valinor\Type\Type;

/** @internal */
final class TypeAliasAssignerSpecification implements TypeParserSpecification
{
    public function __construct(
        /** @var array<string, Type> */
        private array $aliases
    ) {
    }

    public function transform(TypeLexer $lexer): TypeLexer
    {
        return new TypeAliasLexer($lexer, $this->aliases);
    }
}
