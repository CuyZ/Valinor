<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Functional\Utility\Reflection;

use CuyZ\Valinor\Utility\Reflection\PhpParser;
use PHPUnit\Framework\TestCase;
use ReflectionFunction;

final class PhpParserTest extends TestCase
{
    public function test_can_parse_namespace_for_closure_with_one_level_namespace(): void
    {
        $function = require_once 'closure-with-one-level-namespace.php';

        $reflection = new ReflectionFunction($function);

        $namespace = PhpParser::parseNamespace($reflection);

        self::assertSame('OneLevelNamespace', $namespace);
    }

    public function test_can_parse_namespace_for_closure_with_qualified_namespace(): void
    {
        $function = require_once 'closure-with-qualified-namespace.php';

        $reflection = new ReflectionFunction($function);

        $namespace = PhpParser::parseNamespace($reflection);

        self::assertSame('Root\QualifiedNamespace', $namespace);
    }
}
