<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Factory\Specifications;

use CuyZ\Valinor\Type\Parser\Lexer\TypeLexer;

/** @internal */
interface TypeParserSpecification
{
    public function transform(TypeLexer $lexer): TypeLexer;
}
