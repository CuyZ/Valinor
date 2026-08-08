<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Parser\Lexer;

use CuyZ\Valinor\Tests\Unit\UnitTestCase;
use CuyZ\Valinor\Type\Parser\Lexer\TokenizedAnnotation;

final class TokenizedAnnotationTest extends UnitTestCase
{
    public function test_all_between_returns_tokens_within_given_offsets(): void
    {
        // Tokens of an annotation like `@template T of Foo = Bar`.
        $annotation = new TokenizedAnnotation('@template', ['T', ' ', 'of', ' ', 'Foo', ' ', '=', ' ', 'Bar']);

        self::assertSame('Foo ', $annotation->allBetween(4, 6));
    }
}
