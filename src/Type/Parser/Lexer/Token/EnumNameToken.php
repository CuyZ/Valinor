<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Lexer\Token;

use CuyZ\Valinor\Type\Parser\Exception\Enum\MissingEnumCase;
use CuyZ\Valinor\Type\Parser\Exception\Enum\MissingEnumColon;
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
    ) {
    }

    public function traverse(TokenStream $stream): Type
    {
        return $this->findPatternEnumType($stream)
            ?? EnumType::native($this->enumName);
    }

    private function findPatternEnumType(TokenStream $stream): ?Type
    {
        if ($stream->done() || ! $stream->next() instanceof ColonToken) {
            return null;
        }

        $case = $stream->forward();
        $missingColon = true;

        if (! $stream->done()) {
            $case = $stream->forward();

            $missingColon = ! $case instanceof ColonToken;
        }

        if (! $missingColon) {
            if ($stream->done()) {
                throw new MissingEnumCase($this->enumName);
            }

            $case = $stream->forward();
        }

        $symbol = $case->symbol();

        if ($symbol === '*') {
            throw new MissingSpecificEnumCase($this->enumName);
        }

        if ($missingColon) {
            throw new MissingEnumColon($this->enumName, $symbol);
        }

        return EnumType::fromPattern($this->enumName, $symbol);
    }

    public function symbol(): string
    {
        return $this->enumName;
    }
}
