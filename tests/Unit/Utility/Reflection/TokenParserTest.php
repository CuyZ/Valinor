<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Utility\Reflection;

use Closure;
use CuyZ\Valinor\Tests\Fixtures\WithAliasA\ClassA;
use CuyZ\Valinor\Tests\Fixtures\WithAliasB\ClassB;
use CuyZ\Valinor\Tests\Unit\Utility\Reflection\Fixtures\ClassInSingleNamespace;
use CuyZ\Valinor\Tests\Unit\Utility\Reflection\Fixtures\ClassWithImport;
use CuyZ\Valinor\Tests\Unit\Utility\Reflection\Fixtures\SubDir\Bar;
use CuyZ\Valinor\Tests\Unit\Utility\Reflection\Fixtures\SubDir\Foo;
use CuyZ\Valinor\Utility\Reflection\PhpParser;
use CuyZ\Valinor\Utility\Reflection\TokenParser;
use DateTimeImmutable;
use Generator;
use http\Exception\RuntimeException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;
use stdClass;


final class TokenParserTest extends TestCase
{
    public function testGetNamespace(): void
    {
        /** @var Closure $closure */
        $closure = require __DIR__ . '/Fixtures/ClosureForImport.php';
        $reflectionFunction = new ReflectionFunction($closure);
        $parser = new TokenParser(file_get_contents($reflectionFunction->getFileName()));
        $this->assertSame('', $parser->getNamespace($reflectionFunction));
    }

    public function testGetNamespaceMultipleOnOneLine(): void
    {
        /** @var Closure $closure */
        $closure = require __DIR__ . '/Fixtures/MultipleNamespacesClosureForImport.php';
        $reflectionFunction = new ReflectionFunction($closure);
        $parser = new TokenParser(file_get_contents($reflectionFunction->getFileName()));
        $this->expectException(\RuntimeException::class);
        $parser->getNamespace($reflectionFunction);
    }
}
