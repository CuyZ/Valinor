<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Normalizer;

use CuyZ\Valinor\NormalizerBuilder;
use CuyZ\Valinor\Tests\Fixture\Enum\BackedIntegerEnum;
use CuyZ\Valinor\Tests\Fixture\Enum\BackedStringEnum;
use CuyZ\Valinor\Tests\Fixture\Enum\PureEnum;
use DateTimeImmutable;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;
use stdClass;

final class NormalizerTest extends TestCase
{
    /**
     * @dataProvider normalize_basic_values_yields_expected_output_data_provider
     *
     * @param array<int, callable> $callbacks
     */
    public function test_normalize_basic_values_yields_expected_output(mixed $input, mixed $expected, array $callbacks = []): void
    {
        $builder = new NormalizerBuilder();

        foreach ($callbacks as $priority => $callback) {
            $builder = $builder->addHandler($callback, $priority);
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

        yield 'integer' => [
            'input' => 42,
            'expected' => 42,
        ];

        yield 'float' => [
            'input' => 1337.404,
            'expected' => 1337.404,
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

        yield 'class with child properties' => [
            'input' => new SomeChildClass(),
            'output' => [
                'stringFromParentClass' => 'foo',
                'stringFromChildClass' => 'bar',
            ],
        ];

        yield 'class with serialize method' => [
            'input' => new SomeClassWithSerializeMethod(),
            'output' => [
                'some_string' => 'foo',
                'some_integer' => 42,
            ],
        ];

        yield 'date with default normalizer' => [
            'input' => new DateTimeImmutable('1971-11-08'),
            'expected' => '1971-11-08T00:00:00.000000+00:00',
        ];

        yield 'date with custom normalizer' => [
            'input' => new DateTimeImmutable('1971-11-08'),
            'expected' => '1971-11-08',
            'callbacks' => [
                fn (DateTimeInterface $object) => $object->format('Y-m-d')
            ],
        ];

        yield 'object with custom normalizer' => [
            'input' => new BasicObject('foo'),
            'expected' => 'foo!',
            'callbacks' => [
                fn (BasicObject $object) => $object->value . '!',
            ],
        ];

        yield 'object with custom undefined object normalizer' => [
            'input' => new BasicObject('foo'),
            'expected' => 'foo!',
            'callbacks' => [
                fn (object $object) => $object->value . '!', // @phpstan-ignore-line
            ],
        ];

        yield 'object with custom union object normalizer' => [
            'input' => new BasicObject('foo'),
            'expected' => 'foo!',
            'callbacks' => [
                fn (stdClass|BasicObject $object) => $object->value . '!',
            ],
        ];

        yield 'object with custom normalizer calling next' => [
            'input' => new BasicObject('foo'),
            'expected' => [
                'value' => 'foo',
                'bar' => 'bar',
            ],
            'callbacks' => [
                function (object $object, callable $next) {
                    $result = $next();
                    $result['bar'] = 'bar';

                    return $result;
                },
            ],
        ];

        yield 'object with several prioritized normalizers' => [
            'input' => new BasicObject('foo'),
            'expected' => 'foo*!?',
            'callbacks' => [
                -20 => fn (BasicObject $object, callable $next) => $object->value,
                -15 => fn (stdClass $object) => 'bar', // Should be ignored by the normalizer
                -10 => fn (BasicObject $object, callable $next) => $next() . '*',
                0 => fn (BasicObject $object, callable $next) => $next() . '!',
                10 => fn (stdClass $object) => 'baz', // Should be ignored by the normalizer
                20 => fn (BasicObject $object, callable $next) => $next() . '?',
            ],
        ];
    }

    public function test_no_priority_given_is_set_to_0(): void
    {
        $result = (new NormalizerBuilder())
            ->addHandler(fn (object $object) => 'foo', -2)
            ->addHandler(fn (object $object, callable $next) => $next() . '!', -1)
            ->addHandler(fn (object $object, callable $next) => $next() . '?')
            ->addHandler(fn (object $object, callable $next) => $next() . '*', 1)
            ->normalizer()
            ->normalize(new stdClass());

        self::assertSame('foo!?*', $result);
    }

    public function test_no_param_in_callable_throws_exception(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('@todo');

        (new NormalizerBuilder())
            ->addHandler(fn () => 42)
            ->normalizer()
            ->normalize(new stdClass());
    }

    public function test_too_many_params_in_callable_throws_exception(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('@todo');

        (new NormalizerBuilder())
            // @phpstan-ignore-next-line
            ->addHandler(fn (stdClass $object, callable $next, int $unexpectedParameter) => 42)
            ->normalizer()
            ->normalize(new stdClass());
    }

    public function test_second_param_is_not_callable_throws_exception(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('@todo');

        (new NormalizerBuilder())
            // @phpstan-ignore-next-line
            ->addHandler(fn (stdClass $object, int $unexpectedParameterType) => 42)
            ->normalizer()
            ->normalize(new stdClass());
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

final class SomeClassWithSerializeMethod
{
    public string $string = 'foo';
    public int $integer = 42;

    public function __serialize(): array
    {
        return [
            'some_string' => $this->string,
            'some_integer' => $this->integer,
        ];
    }
}
