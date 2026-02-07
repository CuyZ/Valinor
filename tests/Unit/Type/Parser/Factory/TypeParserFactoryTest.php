<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Parser\Factory;

use CuyZ\Valinor\Tests\Unit\UnitTestCase;
use CuyZ\Valinor\Type\Parser\CachedParser;
use CuyZ\Valinor\Type\Parser\Factory\TypeParserFactory;

final class TypeParserFactoryTest extends UnitTestCase
{
    public function test_get_default_parser_returns_same_cached_parser(): void
    {
        $typeParserFactory = new TypeParserFactory();

        $parserA = $typeParserFactory->buildDefaultTypeParser();
        $parserB = $typeParserFactory->buildDefaultTypeParser();

        self::assertInstanceOf(CachedParser::class, $parserA);
        self::assertSame($parserA, $parserB);
    }
}
