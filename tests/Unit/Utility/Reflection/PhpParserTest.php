<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Utility\Reflection;

use CuyZ\Valinor\Tests\Fixtures\WithAliasA\ClassA;
use CuyZ\Valinor\Tests\Fixtures\WithAliasB\ClassB;
use CuyZ\Valinor\Tests\Unit\Utility\Reflection\Fixtures\SingleNamespaceFile;
use CuyZ\Valinor\Tests\Unit\Utility\Reflection\Fixtures\SubDir\Bar;
use CuyZ\Valinor\Tests\Unit\Utility\Reflection\Fixtures\SubDir\Foo;
use CuyZ\Valinor\Utility\Reflection\PhpParser;
use Generator;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;

require_once __DIR__ . '/Fixtures/MultipleNamespaces.php';

final class PhpParserTest extends TestCase
{
    /**
     * @dataProvider useStatementsDataProvider
     * @template T of object
     *
     * @param ReflectionClass<T>|ReflectionFunction|ReflectionMethod $reflection
     * @param array<string, string> $expectedMap
     */
    public function test_parse_use_statements(
        $reflection,
        array $expectedMap
    ): void {
        $parser = new PhpParser();

        $actualMap = $parser->parseUseStatements($reflection);

        self::assertSame($expectedMap, $actualMap);
    }

    public function useStatementsDataProvider(): Generator
    {
        yield 'no use statements' => [
            new ReflectionClass(\stdClass::class),
            []
        ];

        yield 'one namespace' => [
            new ReflectionClass(SingleNamespaceFile::class),
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
            new ReflectionMethod(SingleNamespaceFile::class, '__construct'),
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
                'classb' => ClassB::class,
                'classbalias' => ClassB::class,
                'baralias' => Bar::class,
                'foo' => Foo::class,
                'datetimeimmutable' => \DateTimeImmutable::class,
                'stdclassalias' => \stdClass::class,
                'classa' => ClassA::class,
                'classaalias' => ClassA::class,
            ]
        ];
    }
}
