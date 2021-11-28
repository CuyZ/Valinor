<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Parser\Factory;

use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Tests\Fake\Type\Parser\FakeTypeParser;
use CuyZ\Valinor\Type\Parser\LazyParser;
use CuyZ\Valinor\Type\Parser\TypeParser;
use PHPUnit\Framework\TestCase;

final class LazyParserTest extends TestCase
{
    public function test_delegate_callback_is_called_only_once(): void
    {
        $calls = 0;
        $delegate = new FakeTypeParser();
        $type = new FakeType();
        $delegate->willReturn('foo', $type);

        $typeParser = new LazyParser(static function () use (&$calls, $delegate): TypeParser {
            $calls++;

            return $delegate;
        });

        $resultA = $typeParser->parse('foo');
        $resultB = $typeParser->parse('foo');

        self::assertSame($type, $resultA);
        self::assertSame($type, $resultB);
        self::assertSame(1, $calls);
    }
}
