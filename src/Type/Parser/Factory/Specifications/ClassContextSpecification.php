<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Factory\Specifications;

use CuyZ\Valinor\Type\Parser\Lexer\ClassContextLexer;
use CuyZ\Valinor\Type\Parser\Lexer\TypeLexer;

/** @internal */
final class ClassContextSpecification implements TypeParserSpecification
{
    public function __construct(
        /** @var class-string */
        private string $className
    ) {
    }

    public function transform(TypeLexer $lexer): TypeLexer
    {
        return new ClassContextLexer($lexer, $this->className);
    }
}
