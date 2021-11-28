<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Parser\Lexer;

use AssertionError;
use CuyZ\Valinor\Type\Parser\Lexer\Token\NativeToken;
use PHPUnit\Framework\TestCase;

final class NativeTokenTest extends TestCase
{
    public function test_native_token_instances_are_memoized(): void
    {
        $tokenA = NativeToken::from('string');
        $tokenB = NativeToken::from('string');

        self::assertSame($tokenA, $tokenB);
    }

    public function test_get_unknown_native_token_fails_assertion(): void
    {
        $this->expectException(AssertionError::class);

        NativeToken::from('invalid');
    }
}
