<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Parser\Factory;

use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Tests\Fake\Type\Parser\FakeTypeParser;
use CuyZ\Valinor\Type\Parser\CachedParser;
use PHPUnit\Framework\TestCase;

final class CachedParserTest extends TestCase
{
    public function test_parsed_type_is_cached(): void
    {
        $delegate = new FakeTypeParser();

        $typeA = new FakeType();
        $typeB = new FakeType();

        $typeParser = new CachedParser($delegate);

        $delegate->willReturn('foo', $typeA);
        $resultA = $typeParser->parse('foo');

        $delegate->willReturn('foo', $typeB);
        $resultB = $typeParser->parse('foo');

        self::assertSame($typeA, $resultA);
        self::assertSame($typeA, $resultB);
    }
}
