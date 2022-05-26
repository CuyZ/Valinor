<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fake\Type\Parser\Lexer\Token;

use CuyZ\Valinor\Type\Parser\Lexer\Token\Token;

final class FakeToken implements Token
{
    public function symbol(): string
    {
        return 'fake-token';
    }
}
