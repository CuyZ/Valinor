<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Parser\Factory;

use CuyZ\Valinor\Type\Parser\CachedParser;
use CuyZ\Valinor\Type\Parser\Factory\TypeParserFactory;
use PHPUnit\Framework\TestCase;

final class TypeParserFactoryTest extends TestCase
{
    private TypeParserFactory $typeParserFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->typeParserFactory = new TypeParserFactory();
    }

    public function test_get_default_parser_returns_same_cached_parser(): void
    {
        $parserA = $this->typeParserFactory->buildDefaultTypeParser();
        $parserB = $this->typeParserFactory->buildDefaultTypeParser();

        self::assertInstanceOf(CachedParser::class, $parserA);
        self::assertSame($parserA, $parserB);
    }
}
