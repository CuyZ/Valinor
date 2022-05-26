<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Parser\Lexer\Token;

use CuyZ\Valinor\Tests\Fake\Type\Parser\Factory\FakeTypeParserFactory;
use CuyZ\Valinor\Tests\Fake\Type\Parser\Template\FakeTemplateParser;
use CuyZ\Valinor\Type\Parser\Lexer\Token\GenericClassNameToken;
use PHPUnit\Framework\TestCase;
use stdClass;

final class GenericClassNameTokenTest extends TestCase
{
    public function test_symbol_is_correct(): void
    {
        $token = new GenericClassNameToken(stdClass::class, new FakeTypeParserFactory(), new FakeTemplateParser());

        self::assertSame(stdClass::class, $token->symbol());
    }
}
