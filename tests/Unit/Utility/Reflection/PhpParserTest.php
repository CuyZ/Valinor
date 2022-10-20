<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Utility\Reflection;

use CuyZ\Valinor\Tests\Fixtures\WithAliasA\ClassA;
use CuyZ\Valinor\Tests\Fixtures\WithAliasB\ClassB;
use CuyZ\Valinor\Tests\Unit\Utility\Reflection\Fixtures\ClassInSingleNamespace;
use CuyZ\Valinor\Tests\Unit\Utility\Reflection\Fixtures\SubDir\Bar;
use CuyZ\Valinor\Tests\Unit\Utility\Reflection\Fixtures\SubDir\Foo;
use CuyZ\Valinor\Utility\Reflection\PhpParser;
use Generator;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;

require_once __DIR__ . '/Fixtures/TwoClassesInDifferentNamespaces.php';
require_once __DIR__ . '/Fixtures/FunctionInRootNamespace.php';
require_once __DIR__ . '/Fixtures/FunctionWithSeveralImportStatementsInSameUseStatement.php';
require_once __DIR__ . '/Fixtures/FunctionWithGroupedImportStatements.php';

final class PhpParserTest extends TestCase
{
    /**
     * @dataProvider useStatementsDataProvider
     * @template T of object
     *
     * @param ReflectionClass<T>|ReflectionFunction|ReflectionMethod $reflection
     * @param array<string, string> $expectedMap
     */
    public function test_parse_use_statements(\ReflectionClass|\ReflectionFunction|\ReflectionMethod $reflection, array $expectedMap): void
    {
        $actualMap = PhpParser::parseUseStatements($reflection);

        self::assertSame($expectedMap, $actualMap);
    }

    public function useStatementsDataProvider(): Generator
    {
        yield 'no use statements' => [
            new ReflectionClass(\stdClass::class),
            []
        ];

        yield 'one namespace' => [
            new ReflectionClass(ClassInSingleNamespace::class),
            [
                'baralias' => Bar::class,
                'foo' => Foo::class,
                'datetimeimmutable' => \DateTimeImmutable::class,
                'stdclassalias' => \stdClass::class,
            ]
        ];

        yield 'multiple namespaces, class A' => [
            new ReflectionClass(ClassA::class),
            [
                'classb' => ClassB::class,
                'classbalias' => ClassB::class,
                'baralias' => Bar::class,
                'foo' => Foo::class,
                'datetimeimmutable' => \DateTimeImmutable::class,
                'stdclassalias' => \stdClass::class,
            ]
        ];

        yield 'multiple namespaces, class B' => [
            new ReflectionClass(ClassB::class),
            [
                'classa' => ClassA::class,
                'classaalias' => ClassA::class,
                'baralias' => Bar::class,
                'foo' => Foo::class,
                'datetimeimmutable' => \DateTimeImmutable::class,
                'stdclassalias' => \stdClass::class,
            ]
        ];

        yield 'multiple namespaces, function A' => [
            new ReflectionFunction('\CuyZ\Valinor\Tests\Fixtures\WithAliasA\functionA'),
            [
                'classb' => ClassB::class,
                'classbalias' => ClassB::class,
                'baralias' => Bar::class,
                'foo' => Foo::class,
                'datetimeimmutable' => \DateTimeImmutable::class,
                'stdclassalias' => \stdClass::class,
            ]
        ];

        yield 'multiple namespaces, function B' => [
            new ReflectionFunction('\CuyZ\Valinor\Tests\Fixtures\WithAliasB\functionB'),
            [
                'classa' => ClassA::class,
                'classaalias' => ClassA::class,
                'baralias' => Bar::class,
                'foo' => Foo::class,
                'datetimeimmutable' => \DateTimeImmutable::class,
                'stdclassalias' => \stdClass::class,
            ]
        ];

        yield 'one namespace, method' => [
            new ReflectionMethod(ClassInSingleNamespace::class, '__construct'),
            [
                'baralias' => Bar::class,
                'foo' => Foo::class,
                'datetimeimmutable' => \DateTimeImmutable::class,
                'stdclassalias' => \stdClass::class,
            ]
        ];

        yield 'multiple namespaces, class A method' => [
            new ReflectionMethod(ClassA::class, '__construct'),
            [
                'classb' => ClassB::class,
                'classbalias' => ClassB::class,
                'baralias' => Bar::class,
                'foo' => Foo::class,
                'datetimeimmutable' => \DateTimeImmutable::class,
                'stdclassalias' => \stdClass::class,
            ]
        ];

        yield 'multiple namespaces, class B method' => [
            new ReflectionMethod(ClassB::class, '__construct'),
            [
                'classa' => ClassA::class,
                'classaalias' => ClassA::class,
                'baralias' => Bar::class,
                'foo' => Foo::class,
                'datetimeimmutable' => \DateTimeImmutable::class,
                'stdclassalias' => \stdClass::class,
            ]
        ];

        yield 'function in root namespace' => [
            new ReflectionFunction('function_in_root_namespace'),
            [
                'fooalias' => Foo::class,
                'baralias' => Bar::class,
            ]
        ];

        yield 'one namespace, one use statement, two import statements' => [
            new ReflectionFunction('CuyZ\Valinor\Tests\Unit\Utility\Reflection\Fixtures\function_with_several_import_statements_in_same_use_statement'),
            [
                'fooalias' => Foo::class,
                'baralias' => Bar::class,
                'anotherfooalias' => Foo::class,
                'anotherbaralias' => Bar::class,
            ]
        ];

        yield 'one namespace, one use statement, two grouped import statements' => [
            new ReflectionFunction('\CuyZ\Valinor\Tests\Unit\Utility\Reflection\Fixtures\function_with_grouped_import_statements'),
            [
                'fooalias' => Foo::class,
                'baralias' => Bar::class,
                'anotherfooalias' => Foo::class,
                'anotherbaralias' => Bar::class,
            ],
        ];
    }
}
