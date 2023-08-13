<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Lexer\Token;

use CuyZ\Valinor\Type\Parser\Exception\Enum\MissingEnumCase;
use CuyZ\Valinor\Type\Parser\Exception\Enum\MissingSpecificEnumCase;
use CuyZ\Valinor\Type\Parser\Lexer\TokenStream;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\EnumType;
use UnitEnum;

/** @internal */
final class EnumNameToken implements TraversingToken
{
    public function __construct(
        /** @var class-string<UnitEnum> */
        private string $enumName
    ) {}

    public function traverse(TokenStream $stream): Type
    {
        return $this->findPatternEnumType($stream)
            ?? EnumType::native($this->enumName);
    }

    private function findPatternEnumType(TokenStream $stream): ?Type
    {
        if ($stream->done() || ! $stream->next() instanceof DoubleColonToken) {
            return null;
        }

        $stream->forward();

        if ($stream->done()) {
            throw new MissingEnumCase($this->enumName);
        }

        $symbol = $stream->forward()->symbol();

        if ($symbol === '*') {
            throw new MissingSpecificEnumCase($this->enumName);
        }

        return EnumType::fromPattern($this->enumName, $symbol);
    }

    public function symbol(): string
    {
        return $this->enumName;
    }
}
