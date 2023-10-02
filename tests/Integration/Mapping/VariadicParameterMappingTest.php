<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Tests\Integration\IntegrationTest;

final class VariadicParameterMappingTest extends IntegrationTest
{
    public function test_only_variadic_parameters_are_mapped_properly(): void
    {
        try {
            $object = (new MapperBuilder())->mapper()->map(SomeClassWithOnlyVariadicParameters::class, ['foo', 'bar', 'baz']);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(['foo', 'bar', 'baz'], $object->values);
    }

    public function test_variadic_parameters_are_mapped_properly_when_string_keys_are_given(): void
    {
        try {
            $object = (new MapperBuilder())->mapper()->map(SomeClassWithOnlyVariadicParameters::class, [
                'foo' => 'foo',
                'bar' => 'bar',
                'baz' => 'baz',
            ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(['foo', 'bar', 'baz'], $object->values);
    }

    public function test_constructor_with_variadic_parameters_with_dots_in_dot_blocks_are_defined_properly(): void
    {
        try {
            (new MapperBuilder())
                ->mapper()
                ->map(SomeClassWithVariadicParametersInDocBlock::class, ['']);
        } catch (MappingError $exception) {
            $error = $exception->node()->children()[0]->messages()[0];

            self::assertSame("Value '' is not a valid non-empty string.", (string)$error);
        }
    }

    public function test_non_variadic_and_variadic_parameters_are_mapped_properly(): void
    {
        try {
            $object = (new MapperBuilder())->mapper()->map(SomeClassWithNonVariadicAndVariadicParameters::class, [
                'int' => 42,
                'values' => ['foo', 'bar', 'baz'],
            ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(42, $object->int);
        self::assertSame(['foo', 'bar', 'baz'], $object->values);
    }

    public function test_named_constructor_with_non_variadic_and_variadic_parameters_are_mapped_properly(): void
    {
        try {
            $object = (new MapperBuilder())
                // PHP8.1 first-class callable syntax
                ->registerConstructor([SomeClassWithNamedConstructorWithNonVariadicAndVariadicParameters::class, 'new'])
                ->mapper()
                ->map(SomeClassWithNamedConstructorWithNonVariadicAndVariadicParameters::class, [
                    'int' => 42,
                    'values' => ['foo', 'bar', 'baz'],
                ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(42, $object->int);
        self::assertSame(['foo', 'bar', 'baz'], $object->values);
    }
}

final class SomeClassWithOnlyVariadicParameters
{
    /** @var string[] */
    public array $values;

    public function __construct(string ...$values)
    {
        $this->values = $values;
    }
}

final class SomeClassWithNamedConstructorWithOnlyVariadicParameters
{
    /** @var string[] */
    public array $values;

    private function __construct(string ...$values)
    {
        $this->values = $values;
    }

    public static function new(string ...$values): self
    {
        return new self(...$values);
    }
}

final class SomeClassWithVariadicParametersInDocBlock
{
    /** @var array<non-empty-string> */
    public array $values;

    /**
     * @param non-empty-string ...$values
     */
    public function __construct(string ...$values)
    {
        $this->values = $values;
    }
}

final class SomeClassWithNonVariadicAndVariadicParameters
{
    /** @var string[] */
    public array $values;

    public function __construct(public int $int, string ...$values)
    {
        $this->values = $values;
    }
}

final class SomeClassWithNamedConstructorWithNonVariadicAndVariadicParameters
{
    /** @var string[] */
    public array $values;

    private function __construct(public int $int, string ...$values)
    {
        $this->values = $values;
    }

    public static function new(int $int, string ...$values): self
    {
        return new self($int, ...$values);
    }
}
