<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Type;

use CuyZ\Valinor\Tests\Fixture\Enum\BackedStringEnum;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;
use CuyZ\Valinor\Type\Parser\Factory\Specifications\ObjectSpecification;
use CuyZ\Valinor\Type\Parser\Lexer\NativeLexer;
use CuyZ\Valinor\Type\Parser\Lexer\SpecificationsLexer;
use CuyZ\Valinor\Type\Parser\LexingParser;
use CuyZ\Valinor\Type\Parser\VacantTypeAssignerParser;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\Generics;
use CuyZ\Valinor\Type\Types\GenericType;
use CuyZ\Valinor\Type\Types\MixedType;
use PHPUnit\Framework\Attributes\DataProvider;

use function array_map;

final class GenericInferringTest extends IntegrationTestCase
{
    /**
     * @param array<non-empty-string, non-empty-string> $generics
     * @param array<non-empty-string, non-empty-string> $expectedGenerics
     */
    #[DataProvider('generics_can_be_inferred_from_type_data_provider')]
    public function test_generics_can_be_inferred_from_type(array $generics, string $rawTypeA, string $rawTypeB, array $expectedGenerics): void
    {
        $lexer = new SpecificationsLexer([new ObjectSpecification(mustCheckTemplates: true)]);
        $lexer = new NativeLexer($lexer);
        $parser = new LexingParser($lexer);

        $baseGenerics = [];

        foreach ($generics as $name => $type) {
            $baseGenerics[$name] = new GenericType($name, $parser->parse($type));
        }

        $parser = new VacantTypeAssignerParser($parser, $baseGenerics);

        $typeA = $parser->parse($rawTypeA);
        $typeB = $parser->parse($rawTypeB);

        $inferredGenerics = $typeA->inferGenericsFrom($typeB, new Generics());
        $inferredGenerics = array_map(static fn (Type $type) => $type->toString(), $inferredGenerics->items);

        self::assertSame($expectedGenerics, $inferredGenerics);

        // We also check that the typeB cannot infer a generic from a mixed type
        // as it would be a contravariance violation.
        $inferredGenerics = $typeB->inferGenericsFrom(new MixedType(), new Generics());

        self::assertSame([], $inferredGenerics->items);
    }

    public static function generics_can_be_inferred_from_type_data_provider(): iterable
    {
        $scalars = [
            'scalar',
            'array-key',
            'bool',
            'true',
            'float',
            '1337.42',
            'string',
            'non-empty-string',
            'numeric-string',
            'class-string',
            '"string value"',
            'int',
            'int<42, 1337>',
            'positive-int',
            'negative-int',
            'non-positive-int',
            'non-negative-int',
            '42',
        ];

        foreach ($scalars as $scalar) {
            yield "from $scalar" => [
                'generics' => ['T' => 'scalar'],
                'rawTypeA' => 'T',
                'rawTypeB' => $scalar,
                'expectedGenerics' => [
                    'T' => $scalar,
                ],
            ];
        }

        yield 'from array key' => [
            'generics' => ['T' => 'scalar'],
            'rawTypeA' => 'array<T, string>',
            'rawTypeB' => 'array<int, string>',
            'expectedGenerics' => [
                'T' => 'int',
            ],
        ];

        yield 'from array key with union' => [
            'generics' => ['T' => 'scalar'],
            'rawTypeA' => 'array<T|string, string>',
            'rawTypeB' => 'array<int|non-empty-string, string>',
            'expectedGenerics' => [
                'T' => 'int',
            ],
        ];

        yield 'from array subtype' => [
            'generics' => ['T' => 'scalar'],
            'rawTypeA' => 'array<int, T>',
            'rawTypeB' => 'array<int, string>',
            'expectedGenerics' => [
                'T' => 'string',
            ],
        ];

        yield 'from array subtype with union' => [
            'generics' => ['T' => 'scalar'],
            'rawTypeA' => 'array<int, T|int>',
            'rawTypeB' => 'array<int, string|positive-int>',
            'expectedGenerics' => [
                'T' => 'string',
            ],
        ];

        yield 'from non-empty-array key' => [
            'generics' => ['T' => 'scalar'],
            'rawTypeA' => 'non-empty-array<T, string>',
            'rawTypeB' => 'non-empty-array<int, string>',
            'expectedGenerics' => [
                'T' => 'int',
            ],
        ];

        yield 'from non-empty-array key with union' => [
            'generics' => ['T' => 'scalar'],
            'rawTypeA' => 'non-empty-array<T|string, string>',
            'rawTypeB' => 'non-empty-array<int|non-empty-string, string>',
            'expectedGenerics' => [
                'T' => 'int',
            ],
        ];

        yield 'from non-empty-array subtype' => [
            'generics' => ['T' => 'scalar'],
            'rawTypeA' => 'non-empty-array<int, T>',
            'rawTypeB' => 'non-empty-array<int, string>',
            'expectedGenerics' => [
                'T' => 'string',
            ],
        ];

        yield 'from non-empty-array subtype with union' => [
            'generics' => ['T' => 'scalar'],
            'rawTypeA' => 'non-empty-array<int, T|int>',
            'rawTypeB' => 'non-empty-array<int, string|positive-int>',
            'expectedGenerics' => [
                'T' => 'string',
            ],
        ];

        yield 'from iterable key' => [
            'generics' => ['T' => 'scalar'],
            'rawTypeA' => 'iterable<T, string>',
            'rawTypeB' => 'iterable<int, string>',
            'expectedGenerics' => [
                'T' => 'int',
            ],
        ];

        yield 'from iterable key with union' => [
            'generics' => ['T' => 'scalar'],
            'rawTypeA' => 'iterable<T|string, string>',
            'rawTypeB' => 'iterable<int|non-empty-string, string>',
            'expectedGenerics' => [
                'T' => 'int',
            ],
        ];

        yield 'from iterable subtype' => [
            'generics' => ['T' => 'scalar'],
            'rawTypeA' => 'iterable<int, T>',
            'rawTypeB' => 'iterable<int, string>',
            'expectedGenerics' => [
                'T' => 'string',
            ],
        ];

        yield 'from iterable subtype with union' => [
            'generics' => ['T' => 'scalar'],
            'rawTypeA' => 'iterable<int, T|int>',
            'rawTypeB' => 'iterable<int, string|positive-int>',
            'expectedGenerics' => [
                'T' => 'string',
            ],
        ];

        yield 'from list subtype' => [
            'generics' => ['T' => 'scalar'],
            'rawTypeA' => 'list<T>',
            'rawTypeB' => 'list<string>',
            'expectedGenerics' => [
                'T' => 'string',
            ],
        ];

        yield 'from list subtype with union' => [
            'generics' => ['T' => 'scalar'],
            'rawTypeA' => 'list<T|int>',
            'rawTypeB' => 'list<string|positive-int>',
            'expectedGenerics' => [
                'T' => 'string',
            ],
        ];

        yield 'from non-empty-list subtype' => [
            'generics' => ['T' => 'scalar'],
            'rawTypeA' => 'non-empty-list<T>',
            'rawTypeB' => 'non-empty-list<string>',
            'expectedGenerics' => [
                'T' => 'string',
            ],
        ];

        yield 'from non-empty-list subtype with union' => [
            'generics' => ['T' => 'scalar'],
            'rawTypeA' => 'non-empty-list<T|int>',
            'rawTypeB' => 'non-empty-list<string|positive-int>',
            'expectedGenerics' => [
                'T' => 'string',
            ],
        ];

        yield 'from array shape element' => [
            'generics' => ['T' => 'scalar'],
            'rawTypeA' => 'array{foo: T, bar: int}',
            'rawTypeB' => 'array{foo: string, bar: 42|43}',
            'expectedGenerics' => [
                'T' => 'string',
            ],
        ];

        yield 'from array shape elements' => [
            'generics' => ['T' => 'scalar'],
            'rawTypeA' => 'array{foo: T, bar: int, baz: T}',
            'rawTypeB' => 'array{foo: string, baz: int}',
            'expectedGenerics' => [
                'T' => 'string|int',
            ],
        ];

        yield 'from class' => [
            'generics' => [
                'T1' => 'scalar',
                'T2' => 'scalar',
            ],
            'rawTypeA' => ObjectWithGenerics::class . '<T1, T2>',
            'rawTypeB' => ObjectWithGenerics::class . '<string, int>',
            'expectedGenerics' => [
                'T1' => 'string',
                'T2' => 'int',
            ],
        ];

        yield 'from interface' => [
            'generics' => [
                'T1' => 'scalar',
                'T2' => 'scalar',
            ],
            'rawTypeA' => InterfaceWithGenerics::class . '<T1, T2>',
            'rawTypeB' => InterfaceWithGenerics::class . '<string, int>',
            'expectedGenerics' => [
                'T1' => 'string',
                'T2' => 'int',
            ],
        ];

        yield 'from enum' => [
            'generics' => ['T' => 'mixed'],
            'rawTypeA' => 'string|T',
            'rawTypeB' => BackedStringEnum::class . '::BA*|string',
            'expectedGenerics' => [
                'T' => BackedStringEnum::class . '::BA*',
            ],
        ];

        yield 'from class string' => [
            'generics' => ['T' => 'object'],
            'rawTypeA' => 'class-string<T>',
            'rawTypeB' => 'class-string<stdClass|DateTimeInterface>',
            'expectedGenerics' => [
                'T' => 'stdClass|DateTimeInterface',
            ],
        ];

        yield 'from union' => [
            'generics' => [
                'T1' => 'scalar',
                'T2' => 'scalar',
            ],
            'rawTypeA' => 'T1|T2|string',
            'rawTypeB' => 'bool|non-empty-string|float',
            'expectedGenerics' => [
                'T1' => 'bool|float',
                'T2' => 'bool|float',
            ],
        ];

        yield 'from union inferred with a single type' => [
            'generics' => [
                'T1' => 'scalar',
                'T2' => 'scalar',
            ],
            'rawTypeA' => 'T1|T2|string',
            'rawTypeB' => 'bool',
            'expectedGenerics' => [
                'T1' => 'bool',
                'T2' => 'bool',
            ],
        ];

        yield 'from union containing types with subtypes' => [
            'generics' => [
                'T1' => 'object',
                'T2' => 'object',
            ],
            'rawTypeA' => 'class-string<T1>|class-string<T2>|float',
            'rawTypeB' => 'class-string<stdClass>|float|class-string<DateTimeInterface>',
            'expectedGenerics' => [
                'T1' => 'stdClass|DateTimeInterface',
                'T2' => 'stdClass|DateTimeInterface',
            ],
        ];
    }
}

/**
 * @template T1
 * @template T2
 */
class ObjectWithGenerics {}

class SimpleObject {}

/**
 * @template T1
 * @template T2
 */
interface InterfaceWithGenerics {}
