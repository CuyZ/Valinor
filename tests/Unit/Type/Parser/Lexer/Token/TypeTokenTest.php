<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Parser\Lexer\Token;

use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Type\Parser\Lexer\Token\TypeToken;
use PHPUnit\Framework\TestCase;

final class TypeTokenTest extends TestCase
{
    public function test_symbol_is_correct(): void
    {
        $token = new TypeToken(FakeType::permissive(), 'foo');

        self::assertSame('foo', $token->symbol());
    }
}
