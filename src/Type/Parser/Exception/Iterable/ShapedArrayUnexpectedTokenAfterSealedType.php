<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception\Iterable;

use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use CuyZ\Valinor\Type\Parser\Lexer\Token\Token;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\ShapedArrayElement;
use RuntimeException;

use function array_map;
use function implode;

/** @internal */
final class ShapedArrayUnexpectedTokenAfterSealedType extends RuntimeException implements InvalidType
{
    /**
     * @param array<ShapedArrayElement> $elements
     * @param list<Token> $unexpectedTokens
     */
    public function __construct(array $elements, Type $unsealedType, array $unexpectedTokens)
    {
        $unexpected = implode('', array_map(fn (Token $token) => $token->symbol(), $unexpectedTokens));

        $signature = 'array{';
        $signature .= implode(', ', array_map(fn (ShapedArrayElement $element) => $element->toString(), $elements));
        $signature .= ', ...' . $unsealedType->toString();
        $signature .= $unexpected;

        parent::__construct(
            "Unexpected `$unexpected` after sealed type in shaped array signature `$signature`, expected a `}`.",
        );
    }
}
