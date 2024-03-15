<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Factory\Specifications;

use CuyZ\Valinor\Type\Parser\Factory\TypeParserFactory;
use CuyZ\Valinor\Type\Parser\Lexer\TypeLexer;
use CuyZ\Valinor\Type\Parser\TypeParser;

/** @internal */
interface TypeParserSpecification
{
    public function manipulateLexer(TypeLexer $lexer): TypeLexer;

    public function manipulateParser(TypeParser $parser, TypeParserFactory $typeParserFactory): TypeParser;
}
