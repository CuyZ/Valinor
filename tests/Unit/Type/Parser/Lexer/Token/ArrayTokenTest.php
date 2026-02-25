<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Parser\Lexer\Token;

use CuyZ\Valinor\Tests\Unit\UnitTestCase;
use CuyZ\Valinor\Type\Parser\Lexer\Token\ArrayToken;

final class ArrayTokenTest extends UnitTestCase
{
    public function test_tokens_are_memoized(): void
    {
        $arrayA = ArrayToken::array();
        $arrayB = ArrayToken::array();

        $nonEmptyArrayA = ArrayToken::nonEmptyArray();
        $nonEmptyArrayB = ArrayToken::nonEmptyArray();

        $iterableA = ArrayToken::iterable();
        $iterableB = ArrayToken::iterable();

        self::assertSame($arrayA, $arrayB);
        self::assertSame($nonEmptyArrayA, $nonEmptyArrayB);
        self::assertSame($iterableA, $iterableB);
    }
}
