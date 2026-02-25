<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Parser\Lexer\Token;

use CuyZ\Valinor\Tests\Unit\UnitTestCase;
use CuyZ\Valinor\Type\Parser\Lexer\Token\ListToken;

final class ListTokenTest extends UnitTestCase
{
    public function test_tokens_are_memoized(): void
    {
        $listA = ListToken::list();
        $listB = ListToken::list();

        $nonEmptyListA = ListToken::nonEmptyList();
        $nonEmptyListB = ListToken::nonEmptyList();

        self::assertSame($listA, $listB);
        self::assertSame($nonEmptyListA, $nonEmptyListB);
    }
}
