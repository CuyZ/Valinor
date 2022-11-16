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

    public function test_named_constructor_with_only_variadic_parameters_are_mapped_properly(): void
    {
        try {
            $object = (new MapperBuilder())
                // @PHP8.1 first-class callable syntax
                ->registerConstructor([SomeClassWithNamedConstructorWithOnlyVariadicParameters::class, 'new'])
                ->mapper()
                ->map(SomeClassWithNamedConstructorWithOnlyVariadicParameters::class, ['foo', 'bar', 'baz']);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(['foo', 'bar', 'baz'], $object->values);
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
                // @PHP8.1 first-class callable syntax
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

final class SomeClassWithNonVariadicAndVariadicParameters
{
    public int $int;

    /** @var string[] */
    public array $values;

    public function __construct(int $int, string ...$values)
    {
        $this->int = $int;
        $this->values = $values;
    }
}

final class SomeClassWithNamedConstructorWithNonVariadicAndVariadicParameters
{
    public int $int;

    /** @var string[] */
    public array $values;

    private function __construct(int $int, string ...$values)
    {
        $this->int = $int;
        $this->values = $values;
    }

    public static function new(int $int, string ...$values): self
    {
        return new self($int, ...$values);
    }
}
