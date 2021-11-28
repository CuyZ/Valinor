<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Lexer\Token;

use CuyZ\Valinor\Type\Parser\Lexer\TokenStream;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\EnumType;
use UnitEnum;

final class EnumNameToken implements TraversingToken
{
    /** @var class-string<UnitEnum> */
    private string $enumName;

    /**
     * @param class-string<UnitEnum> $enumName
     */
    public function __construct(string $enumName)
    {
        $this->enumName = $enumName;
    }

    public function traverse(TokenStream $stream): Type
    {
        return new EnumType($this->enumName);
    }
}
