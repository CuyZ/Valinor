<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Parser\Lexer\Token;

use CuyZ\Valinor\Tests\Fake\Type\Parser\Factory\FakeTypeParserFactory;
use CuyZ\Valinor\Type\Parser\Lexer\Token\ClassNameToken;
use CuyZ\Valinor\Type\Parser\Lexer\Token\AdvancedClassNameToken;
use PHPUnit\Framework\TestCase;
use stdClass;

final class AdvancedClassNameTokenTest extends TestCase
{
    public function test_symbol_is_correct(): void
    {
        $token = new AdvancedClassNameToken(new ClassNameToken(stdClass::class), new FakeTypeParserFactory());

        self::assertSame(stdClass::class, $token->symbol());
    }
}
