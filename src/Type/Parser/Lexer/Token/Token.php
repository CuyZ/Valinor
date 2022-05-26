<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Lexer\Token;

/** @internal */
interface Token
{
    public function symbol(): string;
}
