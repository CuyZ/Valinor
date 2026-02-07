<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Parser\Lexer\Token;

use CuyZ\Valinor\Tests\Unit\UnitTestCase;
use CuyZ\Valinor\Type\Parser\Lexer\Token\ClassNameToken;
use stdClass;

final class ClassNameTokenTest extends UnitTestCase
{
    public function test_method_returns_clone(): void
    {
        $token = new ClassNameToken(stdClass::class);
        $tokenThatMustCheckTemplates = $token->mustCheckTemplates();

        self::assertNotSame($token, $tokenThatMustCheckTemplates);
    }
}
