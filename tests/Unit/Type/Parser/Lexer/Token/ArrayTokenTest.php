<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Parser\Lexer\Token;

use CuyZ\Valinor\Type\Parser\Lexer\Token\ArrayToken;
use PHPUnit\Framework\TestCase;

final class ArrayTokenTest extends TestCase
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
