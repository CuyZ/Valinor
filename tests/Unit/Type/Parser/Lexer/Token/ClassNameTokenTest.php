<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Parser\Lexer\Token;

use CuyZ\Valinor\Type\Parser\Lexer\Token\ClassNameToken;
use PHPUnit\Framework\TestCase;
use stdClass;

final class ClassNameTokenTest extends TestCase
{
    public function test_method_returns_clone(): void
    {
        $token = new ClassNameToken(stdClass::class);
        $tokenThatMustCheckTemplates = $token->mustCheckTemplates();

        self::assertNotSame($token, $tokenThatMustCheckTemplates);
    }
}
