<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Factory\Specifications;

use CuyZ\Valinor\Type\Parser\Factory\TypeParserFactory;
use CuyZ\Valinor\Type\Parser\GenericCheckerParser;
use CuyZ\Valinor\Type\Parser\Lexer\Token\TraversingToken;
use CuyZ\Valinor\Type\Parser\TypeParser;

/** @internal */
final class GenericCheckerSpecification implements TypeParserSpecification
{
    public function manipulateToken(TraversingToken $token): TraversingToken
    {
        return $token;
    }

    public function manipulateParser(TypeParser $parser, TypeParserFactory $typeParserFactory): TypeParser
    {
        return new GenericCheckerParser($parser, $typeParserFactory);
    }
}
