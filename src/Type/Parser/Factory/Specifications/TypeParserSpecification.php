<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Factory\Specifications;

use CuyZ\Valinor\Type\Parser\Lexer\Token\TraversingToken;

/** @internal */
interface TypeParserSpecification
{
    public function manipulateToken(TraversingToken $token): TraversingToken;
}
