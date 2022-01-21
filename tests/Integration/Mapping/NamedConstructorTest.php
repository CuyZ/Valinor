<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Integration\IntegrationTest;

final class NamedConstructorTest extends IntegrationTest
{
    public function test_color_from_hex(): void
    {
        try {
            $result = $this->mapperBuilder
                ->mapper()
                ->map(Color::class, 'FF8040');
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(255, $result->red);
        self::assertSame(128, $result->green);
        self::assertSame(64, $result->blue);
    }

    public function test_color_from_rgb(): void
    {
        try {
            $result = $this->mapperBuilder
                ->mapper()
                ->map(Color::class, [
                    'red' => 255,
                    'green' => 128,
                    'blue' => 64,
                ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(255, $result->red);
        self::assertSame(128, $result->green);
        self::assertSame(64, $result->blue);
    }

    public function test_invalid_named_constructor_are_ignored(): void
    {
        try {
            $result = $this->mapperBuilder
                ->mapper()
                ->map(SomeClassWithInvalidNamedConstructors::class, [
                    'string' => 'foo',
                    'int' => 42,
                ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $result->string);
        self::assertSame(42, $result->int);
    }

    public function test_named_constructor_returns_child_a(): void
    {
        try {
            $result = $this->mapperBuilder
                ->mapper()
                ->map(SomeClassWithChildren::class, 'A');
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertInstanceOf(ChildClassA::class, $result);
    }

    public function test_named_constructor_returns_child_b(): void
    {
        try {
            $result = $this->mapperBuilder
                ->mapper()
                ->map(SomeClassWithChildren::class, 'B');
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertInstanceOf(ChildClassB::class, $result);
    }

    public function test_constructor_with_optional_parameter_is_used_correctly(): void
    {
        try {
            $result = $this->mapperBuilder
                ->mapper()
                ->map(SomeClassWithConstructorWithOptionalParameter::class, [
                    'string' => 'foo',
                ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $result->string);
        self::assertSame(42, $result->int);
    }

    public function test_input_not_matching_constructors_throws_exception(): void
    {
        try {
            $this->mapperBuilder
                ->mapper()
                ->map(SomeClassWithConstructorWithOptionalParameter::class, [
                    'bool' => true,
                ]);
        } catch (MappingError $exception) {
            $error = $exception->node()->messages()[0];

            self::assertSame('1642183169', $error->code());
            self::assertSame('Invalid value, got `array` but expected one of `array{string: string, int?: int}`, `array{string: string, float: float}`.', (string)$error);
        }
    }

    public function test_identical_constructors_throws_exception(): void
    {
        try {
            $this->mapperBuilder
                ->mapper()
                ->map(SomeClassWithIdenticalNamedConstructor::class, [
                    'string' => 'foo',
                    'int' => 42,
                ]);
        } catch (MappingError $exception) {
            $error = $exception->node()->messages()[0];

            self::assertSame('1642787246', $error->code());
            self::assertSame('Could not map input of type `array`.', (string)$error);
        }
    }
}

final class Color
{
    public int $red;

    public int $green;

    public int $blue;

    /**
     * @PHP8.0 Promoted properties
     * @PHP8.1 Readonly properties
     */
    private function __construct(int $red, int $green, int $blue)
    {
        $this->red = $red;
        $this->green = $green;
        $this->blue = $blue;
    }

    public static function fromRgb(int $red, int $green, int $blue): self
    {
        return new self($red, $green, $blue);
    }

    public static function fromHex(string $hex): self
    {
        $red = hexdec(substr($hex, 0, 2));
        $green = hexdec(substr($hex, 2, 2));
        $blue = hexdec(substr($hex, 4, 2));

        return new self((int)$red, (int)$green, (int)$blue);
    }
}

final class SomeClassWithInvalidNamedConstructors
{
    public string $string;

    public int $int;

    /**
     * @PHP8.0 Promoted properties
     * @PHP8.1 Readonly properties
     */
    public function __construct(string $string, int $int)
    {
        $this->string = $string;
        $this->int = $int;
    }

    protected static function privateNamedConstructor(string $string, int $int): self
    {
        return new self($string . '!', $int + 1);
    }

    public static function namedConstructorWithNoParameter(): self
    {
        return new self('bar', 1337);
    }
}

final class SomeClassWithConstructorWithOptionalParameter
{
    public string $string;

    public int $int;

    /**
     * @PHP8.0 Promoted properties
     * @PHP8.1 Readonly properties
     */
    public function __construct(string $string, int $int = 42)
    {
        $this->string = $string;
        $this->int = $int;
    }

    public static function namedConstructor(string $string, float $float): self
    {
        return new self($string, (int)$float);
    }
}

class SomeClassWithChildren
{
    /**
     * @PHP8.0 Use native union
     * @return ChildClassA|ChildClassB
     */
    public static function new(string $foo): self
    {
        return $foo === 'A'
            ? new ChildClassA()
            : new ChildClassB();
    }
}

class ChildClassA extends SomeClassWithChildren
{
}

class ChildClassB extends SomeClassWithChildren
{
}

final class SomeClassWithIdenticalNamedConstructor
{
    public string $string;

    public int $int;

    /**
     * @PHP8.0 Promoted properties
     * @PHP8.1 Readonly properties
     */
    public function __construct(string $string, int $int)
    {
        $this->string = $string;
        $this->int = $int;
    }

    public static function constructorA(string $string): self
    {
        return new self($string, 42);
    }

    // Has the same parameters as `__construct`
    public static function constructorB(string $string, int $int): self
    {
        return new self($string, $int);
    }
}
