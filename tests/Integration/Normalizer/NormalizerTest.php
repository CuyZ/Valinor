<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Normalizer;

use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Normalizer\Exception\CircularReferenceFoundDuringNormalization;
use CuyZ\Valinor\Normalizer\Exception\TransformerHasInvalidCallableParameter;
use CuyZ\Valinor\Normalizer\Exception\TransformerHasNoParameter;
use CuyZ\Valinor\Normalizer\Exception\TransformerHasTooManyParameters;
use CuyZ\Valinor\Normalizer\Exception\TypeUnhandledByNormalizer;
use CuyZ\Valinor\Tests\Fixture\Enum\BackedIntegerEnum;
use CuyZ\Valinor\Tests\Fixture\Enum\BackedStringEnum;
use CuyZ\Valinor\Tests\Fixture\Enum\PureEnum;
use DateTimeImmutable;
use DateTimeInterface;
use IteratorAggregate;
use PHPUnit\Framework\TestCase;
use stdClass;
use Traversable;

use function array_merge;

final class NormalizerTest extends TestCase
{
    /**
     * @dataProvider normalize_basic_values_yields_expected_output_data_provider
     *
     * @param array<int, list<callable>> $transformers
     */
    public function test_normalize_basic_values_yields_expected_output(mixed $input, mixed $expected, array $transformers = []): void
    {
        $builder = new MapperBuilder();

        foreach ($transformers as $priority => $transformersList) {
            foreach ($transformersList as $transformer) {
                $builder = $builder->registerTransformer($transformer, $priority);
            }
        }

        $result = $builder->normalizer()->normalize($input);

        self::assertSame($expected, $result);
    }

    public function normalize_basic_values_yields_expected_output_data_provider(): iterable
    {
        yield 'null' => [
            'input' => null,
            'expected' => null,
        ];

        yield 'string' => [
            'input' => 'foo bar',
            'expected' => 'foo bar',
        ];

        yield 'string with transformer' => [
            'input' => 'foo',
            'expected' => 'foo!',
            'transformers' => [
                [fn (string $value) => $value . '!'],
            ],
        ];

        yield 'integer' => [
            'input' => 42,
            'expected' => 42,
        ];

        yield 'integer with transformer' => [
            'input' => 42,
            'expected' => 43,
            'transformers' => [
                [fn (int $value) => $value + 1],
            ],
        ];

        yield 'integer with negative-int transformer' => [
            'input' => 42,
            'expected' => 42,
            'transformers' => [
                [
                    /** @param negative-int $value */
                    fn (int $value) => $value + 1
                ],
            ],
        ];

        yield 'float' => [
            'input' => 1337.404,
            'expected' => 1337.404,
        ];

        yield 'float with transformer' => [
            'input' => 1337.404,
            'expected' => 1337.405,
            'transformers' => [
                [fn (float $value) => $value + 0.001],
            ],
        ];

        yield 'boolean' => [
            'input' => true,
            'expected' => true,
        ];

        yield 'array of scalar' => [
            'input' => [
                'string' => 'foo',
                'integer' => 42,
                'float' => 1337.404,
                'boolean' => true,
            ],
            'expected' => [
                'string' => 'foo',
                'integer' => 42,
                'float' => 1337.404,
                'boolean' => true,
            ],
        ];

        yield 'array with transformer' => [
            'input' => ['foo'],
            'expected' => ['foo', 'bar'],
            'transformers' => [
                [fn (array $value) => array_merge($value, ['bar'])],
            ],
        ];

        yield 'iterable of scalar' => [
            'input' => (function (): iterable {
                yield 'string' => 'foo';
                yield 'integer' => 42;
                yield 'float' => 1337.404;
                yield 'boolean' => true;
            })(),
            'expected' => [
                'string' => 'foo',
                'integer' => 42,
                'float' => 1337.404,
                'boolean' => true,
            ],
        ];

        yield 'stdClass' => [
            'input' => (function () {
                $object = new stdClass();
                $object->foo = 'foo';
                $object->bar = 'bar';

                return $object;
            })(),
            'expected' => [
                'foo' => 'foo',
                'bar' => 'bar',
            ],
        ];

        yield 'array of object' => [
            'input' => [
                'foo' => new BasicObject('foo'),
                'bar' => new BasicObject('bar'),
            ],
            'expected' => [
                'foo' => ['value' => 'foo'],
                'bar' => ['value' => 'bar'],
            ],
        ];

        if (PHP_VERSION_ID >= 8_01_00) {
            yield 'unit enum' => [
                'input' => PureEnum::FOO,
                'expected' => 'FOO',
            ];

            yield 'backed string enum' => [
                'input' => BackedStringEnum::FOO,
                'expected' => 'foo',
            ];

            yield 'backed integer enum' => [
                'input' => BackedIntegerEnum::FOO,
                'expected' => 42,
            ];
        }

        yield 'class with public properties' => [
            'input' => new class () {
                public string $string = 'foo';
                public int $integer = 42;
                public float $float = 1337.404;
                public bool $boolean = true;
            },
            'output' => [
                'string' => 'foo',
                'integer' => 42,
                'float' => 1337.404,
                'boolean' => true,
            ],
        ];

        yield 'class with protected properties' => [
            'input' => new class () {
                protected string $string = 'foo';
                protected int $integer = 42;
                protected float $float = 1337.404;
                protected bool $boolean = true;
            },
            'output' => [
                'string' => 'foo',
                'integer' => 42,
                'float' => 1337.404,
                'boolean' => true,
            ],
        ];

        yield 'class with private properties' => [
            'input' => new class () {
                private string $string = 'foo'; // @phpstan-ignore-line
                private int $integer = 42; // @phpstan-ignore-line
                private float $float = 1337.404; // @phpstan-ignore-line
                private bool $boolean = true; // @phpstan-ignore-line
            },
            'output' => [
                'string' => 'foo',
                'integer' => 42,
                'float' => 1337.404,
                'boolean' => true,
            ],
        ];

        yield 'class with inherited properties' => [
            'input' => new SomeChildClass(),
            'output' => [
                'stringFromParentClass' => 'foo',
                'stringFromChildClass' => 'bar',
            ],
        ];

        yield 'iterable class' => [
            'input' => new class () implements IteratorAggregate {
                public string $foo = 'foo';
                public string $bar = 'bar';

                public function getIterator(): Traversable
                {
                    yield 'baz' => 'baz';
                }
            },
            'output' => [
                'foo' => 'foo',
                'bar' => 'bar',
            ],
        ];

        yield 'date with default transformer' => [
            'input' => new DateTimeImmutable('1971-11-08'),
            'expected' => '1971-11-08T00:00:00.000000+00:00',
        ];

        yield 'date with transformer' => [
            'input' => new DateTimeImmutable('1971-11-08'),
            'expected' => '1971-11-08',
            'transformers' => [
                [fn (DateTimeInterface $object) => $object->format('Y-m-d')],
            ],
        ];

        yield 'object with transformer' => [
            'input' => new BasicObject('foo'),
            'expected' => 'foo!',
            'transformers' => [
                [fn (BasicObject $object) => $object->value . '!'],
            ],
        ];

        yield 'object with undefined object transformer' => [
            'input' => new BasicObject('foo'),
            'expected' => 'foo!',
            'transformers' => [
                [fn (object $object) => $object->value . '!'], // @phpstan-ignore-line
            ],
        ];

        yield 'iterable class with transformer' => [
            'input' => new class () implements IteratorAggregate {
                public string $foo = 'foo';
                public string $bar = 'bar';

                public function getIterator(): Traversable
                {
                    yield 'baz' => 'baz';
                }
            },
            'output' => 'value',
            'transformers' => [
                [fn (object $object) => 'value'],
            ],
        ];

        yield 'object with union object transformer' => [
            'input' => new BasicObject('foo'),
            'expected' => 'foo!',
            'transformers' => [
                [fn (stdClass|BasicObject $object) => $object->value . '!'],
            ],
        ];

        yield 'object with transformer calling next' => [
            'input' => new BasicObject('foo'),
            'expected' => [
                'value' => 'foo',
                'bar' => 'bar',
            ],
            'transformers' => [[
                function (object $object, callable $next) {
                    $result = $next();
                    $result['bar'] = 'bar';

                    return $result;
                }
            ]],
        ];

        yield 'object with several prioritized transformers' => [
            'input' => new BasicObject('foo'),
            'expected' => 'foo*!?',
            'transformers' => [
                -20 => [fn (BasicObject $object, callable $next) => $object->value],
                -15 => [fn (stdClass $object) => 'bar'], // Should be ignored by the normalizer
                -10 => [fn (BasicObject $object, callable $next) => $next() . '*'],
                0 => [fn (BasicObject $object, callable $next) => $next() . '!'],
                10 => [fn (stdClass $object) => 'baz'], // Should be ignored by the normalizer
                20 => [fn (BasicObject $object, callable $next) => $next() . '?'],
            ],
        ];

        yield 'object with several prioritized transformers with same priority' => [
            'input' => new BasicObject('foo'),
            'expected' => 'foo?!*',
            'transformers' => [
                10 => [
                    fn (BasicObject $object, callable $next) => $next() . '*',
                    fn (BasicObject $object, callable $next) => $next() . '!',
                    fn (BasicObject $object, callable $next) => $object->value . '?',
                ],
            ],
        ];
    }

    public function test_no_priority_given_is_set_to_0(): void
    {
        $result = (new MapperBuilder())
            ->registerTransformer(fn (object $object) => 'foo', -2)
            ->registerTransformer(fn (object $object, callable $next) => $next() . '!', -1)
            ->registerTransformer(fn (object $object, callable $next) => $next() . '?')
            ->registerTransformer(fn (object $object, callable $next) => $next() . '*', 1)
            ->normalizer()
            ->normalize(new stdClass());

        self::assertSame('foo!?*', $result);
    }

    public function test_no_param_in_callable_throws_exception(): void
    {
        $this->expectException(TransformerHasNoParameter::class);
        $this->expectExceptionCode(1695064946);
        $this->expectExceptionMessageMatches('/Transformer must have at least one parameter, none given for `.*`\./');

        (new MapperBuilder())
            ->registerTransformer(fn () => 42)
            ->normalizer()
            ->normalize(new stdClass());
    }

    public function test_too_many_params_in_callable_throws_exception(): void
    {
        $this->expectException(TransformerHasTooManyParameters::class);
        $this->expectExceptionCode(1695065433);
        $this->expectExceptionMessageMatches('/Transformer must have at most 2 parameters, 3 given for `.*`\./');

        (new MapperBuilder())
            ->registerTransformer(fn (stdClass $object, callable $next, int $unexpectedParameter) => 42)
            ->normalizer()
            ->normalize(new stdClass());
    }

    public function test_second_param_is_not_callable_throws_exception(): void
    {
        $this->expectException(TransformerHasInvalidCallableParameter::class);
        $this->expectExceptionCode(1695065710);
        $this->expectExceptionMessageMatches('/Transformer\'s second parameter must be a callable, `int` given for `.*`\./');

        (new MapperBuilder())
            ->registerTransformer(fn (stdClass $object, int $unexpectedParameterType) => 42)
            ->normalizer()
            ->normalize(new stdClass());
    }

    public function test_object_circular_reference_is_detected_and_throws_exception(): void
    {
        $this->expectException(CircularReferenceFoundDuringNormalization::class);
        $this->expectExceptionCode(1695064016);
        $this->expectExceptionMessage('A circular reference was detected with an object of type `' . ObjectWithCircularReferenceA::class . '`. Circular references are not supported by the normalizer');

        $a = new ObjectWithCircularReferenceA();
        $b = new ObjectWithCircularReferenceB();
        $a->b = $b;
        $b->a = $a;

        (new MapperBuilder())->normalizer()->normalize($a);
    }

    public function test_unhandled_type_throws_exception(): void
    {
        $this->expectException(TypeUnhandledByNormalizer::class);
        $this->expectExceptionCode(1695062925);
        $this->expectExceptionMessage('Value of type `Closure` cannot be normalized.');

        (new MapperBuilder())->normalizer()->normalize(fn () => 42);
    }
}

final class BasicObject
{
    public function __construct(public string $value) {}
}

class SomeParentClass
{
    public string $stringFromParentClass = 'foo';
}

final class SomeChildClass extends SomeParentClass
{
    public string $stringFromChildClass = 'bar';
}

final class ObjectWithCircularReferenceA
{
    public ObjectWithCircularReferenceB $b;
}

final class ObjectWithCircularReferenceB
{
    public ObjectWithCircularReferenceA $a;
}
