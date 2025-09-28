<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Factory\Specifications;

use CuyZ\Valinor\Type\Parser\Lexer\Token\ClassNameToken;
use CuyZ\Valinor\Type\Parser\Lexer\Token\EnumNameToken;
use CuyZ\Valinor\Type\Parser\Lexer\Token\ObjectToken;
use CuyZ\Valinor\Type\Parser\Lexer\Token\TraversingToken;
use CuyZ\Valinor\Utility\Reflection\Reflection;

/** @internal */
final class ObjectSpecification implements TypeParserSpecification
{
    public function __construct(
        private bool $mustCheckTemplates,
    ) {}

    public function manipulateToken(TraversingToken $token): TraversingToken
    {
        $symbol = $token->symbol();

        if (! $token instanceof ObjectToken) {
            if (Reflection::enumExists($symbol)) {
                $token = new ObjectToken(new EnumNameToken($symbol));
            } elseif (Reflection::classOrInterfaceExists($symbol)) {
                $token = new ObjectToken(new ClassNameToken($symbol));
            }
        }

        if ($this->mustCheckTemplates && $token instanceof ObjectToken && $token->subToken instanceof ClassNameToken) {
            $token = new ObjectToken($token->subToken->mustCheckTemplates());
        }

        return $token;
    }
}
