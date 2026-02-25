<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Parser;

use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Tests\Fake\Type\Parser\FakeTypeParser;
use CuyZ\Valinor\Tests\Unit\UnitTestCase;
use CuyZ\Valinor\Type\Parser\CachedParser;

final class CachedParserTest extends UnitTestCase
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
