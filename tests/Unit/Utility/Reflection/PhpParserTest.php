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
use PHPUnit\Framework\Attributes\DataProvider;
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
     * @param ReflectionClass<object>|ReflectionFunction|ReflectionMethod $reflection
     * @param array<non-empty-string, array{fqcn: non-empty-string, isExplicitAlias: bool}> $expectedMap
     */
    #[DataProvider('use_statements_data_provider')]
    public function test_parse_use_statements(\ReflectionClass|\ReflectionFunction|\ReflectionMethod $reflection, array $expectedMap): void
    {
        $actualMap = PhpParser::parseUseStatements($reflection);

        self::assertSame($expectedMap, $actualMap);
    }

    public static function use_statements_data_provider(): Generator
    {
        yield 'no use statements' => [
            new ReflectionClass(\stdClass::class),
            []
        ];

        yield 'one namespace' => [
            new ReflectionClass(ClassInSingleNamespace::class),
            [
                'baralias' => [
                    'fqcn' => Bar::class,
                    'isExplicitAlias' => true,
                ],
                'foo' => [
                    'fqcn' => Foo::class,
                    'isExplicitAlias' => false,
                ],
                'datetimeimmutable' => [
                    'fqcn' => \DateTimeImmutable::class,
                    'isExplicitAlias' => false,
                ],
                'stdclassalias' => [
                    'fqcn' => \stdClass::class,
                    'isExplicitAlias' => true,
                ],
            ]
        ];

        yield 'multiple namespaces, class A' => [
            new ReflectionClass(ClassA::class),
            [
                'classb' => [
                    'fqcn' => ClassB::class,
                    'isExplicitAlias' => false,
                ],
                'classbalias' => [
                    'fqcn' => ClassB::class,
                    'isExplicitAlias' => true,
                ],
                'baralias' => [
                    'fqcn' => Bar::class,
                    'isExplicitAlias' => true,
                ],
                'foo' => [
                    'fqcn' => Foo::class,
                    'isExplicitAlias' => false,
                ],
                'datetimeimmutable' => [
                    'fqcn' => \DateTimeImmutable::class,
                    'isExplicitAlias' => false,
                ],
                'stdclassalias' => [
                    'fqcn' => \stdClass::class,
                    'isExplicitAlias' => true,
                ],
            ]
        ];

        yield 'multiple namespaces, class B' => [
            new ReflectionClass(ClassB::class),
            [
                'classa' => [
                    'fqcn' => ClassA::class,
                    'isExplicitAlias' => false,
                ],
                'classaalias' => [
                    'fqcn' => ClassA::class,
                    'isExplicitAlias' => true,
                ],
                'baralias' => [
                    'fqcn' => Bar::class,
                    'isExplicitAlias' => true,
                ],
                'foo' => [
                    'fqcn' => Foo::class,
                    'isExplicitAlias' => false,
                ],
                'datetimeimmutable' => [
                    'fqcn' => \DateTimeImmutable::class,
                    'isExplicitAlias' => false,
                ],
                'stdclassalias' => [
                    'fqcn' => \stdClass::class,
                    'isExplicitAlias' => true,
                ],
            ]
        ];

        yield 'multiple namespaces, function A' => [
            new ReflectionFunction('\CuyZ\Valinor\Tests\Fixtures\WithAliasA\functionA'),
            [
                'classb' => [
                    'fqcn' => ClassB::class,
                    'isExplicitAlias' => false,
                ],
                'classbalias' => [
                    'fqcn' => ClassB::class,
                    'isExplicitAlias' => true,
                ],
                'baralias' => [
                    'fqcn' => Bar::class,
                    'isExplicitAlias' => true,
                ],
                'foo' => [
                    'fqcn' => Foo::class,
                    'isExplicitAlias' => false,
                ],
                'datetimeimmutable' => [
                    'fqcn' => \DateTimeImmutable::class,
                    'isExplicitAlias' => false,
                ],
                'stdclassalias' => [
                    'fqcn' => \stdClass::class,
                    'isExplicitAlias' => true,
                ],
            ]
        ];

        yield 'multiple namespaces, function B' => [
            new ReflectionFunction('\CuyZ\Valinor\Tests\Fixtures\WithAliasB\functionB'),
            [
                'classa' => [
                    'fqcn' => ClassA::class,
                    'isExplicitAlias' => false,
                ],
                'classaalias' => [
                    'fqcn' => ClassA::class,
                    'isExplicitAlias' => true,
                ],
                'baralias' => [
                    'fqcn' => Bar::class,
                    'isExplicitAlias' => true,
                ],
                'foo' => [
                    'fqcn' => Foo::class,
                    'isExplicitAlias' => false,
                ],
                'datetimeimmutable' => [
                    'fqcn' => \DateTimeImmutable::class,
                    'isExplicitAlias' => false,
                ],
                'stdclassalias' => [
                    'fqcn' => \stdClass::class,
                    'isExplicitAlias' => true,
                ],
            ]
        ];

        yield 'one namespace, method' => [
            new ReflectionMethod(ClassInSingleNamespace::class, '__construct'),
            [
                'baralias' => [
                    'fqcn' => Bar::class,
                    'isExplicitAlias' => true,
                ],
                'foo' => [
                    'fqcn' => Foo::class,
                    'isExplicitAlias' => false,
                ],
                'datetimeimmutable' => [
                    'fqcn' => \DateTimeImmutable::class,
                    'isExplicitAlias' => false,
                ],
                'stdclassalias' => [
                    'fqcn' => \stdClass::class,
                    'isExplicitAlias' => true,
                ],
            ]
        ];

        yield 'multiple namespaces, class A method' => [
            new ReflectionMethod(ClassA::class, '__construct'),
            [
                'classb' => [
                    'fqcn' => ClassB::class,
                    'isExplicitAlias' => false,
                ],
                'classbalias' => [
                    'fqcn' => ClassB::class,
                    'isExplicitAlias' => true,
                ],
                'baralias' => [
                    'fqcn' => Bar::class,
                    'isExplicitAlias' => true,
                ],
                'foo' => [
                    'fqcn' => Foo::class,
                    'isExplicitAlias' => false,
                ],
                'datetimeimmutable' => [
                    'fqcn' => \DateTimeImmutable::class,
                    'isExplicitAlias' => false,
                ],
                'stdclassalias' => [
                    'fqcn' => \stdClass::class,
                    'isExplicitAlias' => true,
                ],
            ]
        ];

        yield 'multiple namespaces, class B method' => [
            new ReflectionMethod(ClassB::class, '__construct'),
            [
                'classa' => [
                    'fqcn' => ClassA::class,
                    'isExplicitAlias' => false,
                ],
                'classaalias' => [
                    'fqcn' => ClassA::class,
                    'isExplicitAlias' => true,
                ],
                'baralias' => [
                    'fqcn' => Bar::class,
                    'isExplicitAlias' => true,
                ],
                'foo' => [
                    'fqcn' => Foo::class,
                    'isExplicitAlias' => false,
                ],
                'datetimeimmutable' => [
                    'fqcn' => \DateTimeImmutable::class,
                    'isExplicitAlias' => false,
                ],
                'stdclassalias' => [
                    'fqcn' => \stdClass::class,
                    'isExplicitAlias' => true,
                ],
            ]
        ];

        yield 'function in root namespace' => [
            new ReflectionFunction('function_in_root_namespace'),
            [
                'fooalias' => [
                    'fqcn' => Foo::class,
                    'isExplicitAlias' => true,
                ],
                'baralias' => [
                    'fqcn' => Bar::class,
                    'isExplicitAlias' => true,
                ],
            ]
        ];

        yield 'one namespace, one use statement, two import statements' => [
            new ReflectionFunction('CuyZ\Valinor\Tests\Unit\Utility\Reflection\Fixtures\function_with_several_import_statements_in_same_use_statement'),
            [
                'fooalias' => [
                    'fqcn' => Foo::class,
                    'isExplicitAlias' => true,
                ],
                'baralias' => [
                    'fqcn' => Bar::class,
                    'isExplicitAlias' => true,
                ],
                'anotherfooalias' => [
                    'fqcn' => Foo::class,
                    'isExplicitAlias' => true,
                ],
                'anotherbaralias' => [
                    'fqcn' => Bar::class,
                    'isExplicitAlias' => true,
                ],
            ]
        ];

        yield 'one namespace, one use statement, two grouped import statements' => [
            new ReflectionFunction('\CuyZ\Valinor\Tests\Unit\Utility\Reflection\Fixtures\function_with_grouped_import_statements'),
            [
                'fooalias' => [
                    'fqcn' => Foo::class,
                    'isExplicitAlias' => true,
                ],
                'baralias' => [
                    'fqcn' => Bar::class,
                    'isExplicitAlias' => true,
                ],
                'anotherfooalias' => [
                    'fqcn' => Foo::class,
                    'isExplicitAlias' => true,
                ],
                'anotherbaralias' => [
                    'fqcn' => Bar::class,
                    'isExplicitAlias' => true,
                ],
            ],
        ];
    }
}
