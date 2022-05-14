<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Object\Exception\CannotInstantiateObject;
use CuyZ\Valinor\Tests\Integration\IntegrationTest;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DomainException;
use stdClass;

use function get_class;

final class ConstructorRegistrationMappingTest extends IntegrationTest
{
    public function test_registered_anonymous_function_constructor_is_used(): void
    {
        $object = new stdClass();

        try {
            $result = $this->mapperBuilder
                ->registerConstructor(fn (): stdClass => $object)
                ->mapper()
                ->map(stdClass::class, []);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame($object, $result);
    }

    public function test_registered_anonymous_function_constructor_with_docblock_is_used(): void
    {
        $object = new stdClass();
        $class = new class () {
            public stdClass $object;
        };

        try {
            $result = $this->mapperBuilder
                ->registerConstructor(/** @return stdClass */ fn () => $object)
                ->mapper()
                ->map(get_class($class), []);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame($object, $result->object);
    }

    public function test_registered_named_constructor_is_used(): void
    {
        try {
            $result = $this->mapperBuilder
                // @PHP8.1 first-class callable syntax
                ->registerConstructor([SomeClassWithNamedConstructors::class, 'namedConstructor'])
                ->mapper()
                ->map(SomeClassWithNamedConstructors::class, 'foo');
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $result->foo);
    }

    public function test_registered_callable_object_constructor_is_used(): void
    {
        $constructor = new class () {
            public function __invoke(): stdClass
            {
                $object = new stdClass();
                $object->foo = 'foo';

                return $object;
            }
        };

        try {
            $result = $this->mapperBuilder
                ->registerConstructor($constructor)
                ->mapper()
                ->map(stdClass::class, []);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $result->foo);
    }

    public function test_registered_method_constructor_is_used(): void
    {
        $constructor = new class () {
            public function build(): stdClass
            {
                $object = new stdClass();
                $object->foo = 'foo';

                return $object;
            }
        };

        try {
            $result = $this->mapperBuilder
                // @PHP8.1 first-class callable syntax
                ->registerConstructor([$constructor, 'build'])
                ->mapper()
                ->map(stdClass::class, []);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $result->foo);
    }

    public function test_native_constructor_is_not_called_if_not_registered_but_other_constructors_are_registered(): void
    {
        try {
            $result = $this->mapperBuilder
                // @PHP8.1 first-class callable syntax
                ->registerConstructor([SomeClassWithSimilarNativeConstructorAndNamedConstructor::class, 'namedConstructor'])
                ->mapper()
                ->map(SomeClassWithSimilarNativeConstructorAndNamedConstructor::class, 'foo');
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('value from named constructor', $result->foo);
    }

    public function test_registered_native_constructor_is_called_if_registered_and_other_constructors_are_registered(): void
    {
        try {
            $result = $this->mapperBuilder
                // @PHP8.1 first-class callable syntax
                ->registerConstructor(SomeClassWithDifferentNativeConstructorAndNamedConstructor::class)
                ->registerConstructor([SomeClassWithDifferentNativeConstructorAndNamedConstructor::class, 'namedConstructor'])
                ->mapper()
                ->map(SomeClassWithDifferentNativeConstructorAndNamedConstructor::class, [
                    'foo' => 'foo',
                    'bar' => 1337,
                ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $result->foo);
        self::assertSame(1337, $result->bar);
    }

    public function test_registered_constructor_is_used_when_not_the_first_nor_last_one(): void
    {
        $object = new stdClass();

        try {
            $result = $this->mapperBuilder
                ->registerConstructor(fn (): DateTime => new DateTime())
                // This constructor is surrounded by other ones to ensure it is
                // still used correctly.
                ->registerConstructor(fn (): stdClass => $object)
                ->registerConstructor(fn (): DateTimeImmutable => new DateTimeImmutable())
                ->mapper()
                ->map(stdClass::class, []);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame($object, $result);
    }

    public function test_registered_constructor_with_one_argument_is_used(): void
    {
        try {
            $result = $this->mapperBuilder
                ->registerConstructor(function (int $int): stdClass {
                    $class = new stdClass();
                    $class->int = $int;

                    return $class;
                })
                ->mapper()
                ->map(stdClass::class, 1337);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame(1337, $result->int);
    }

    public function test_registered_constructor_with_several_arguments_is_used(): void
    {
        try {
            $result = $this->mapperBuilder
                ->registerConstructor(function (string $string, int $int, float $float = 1337.404): stdClass {
                    $class = new stdClass();
                    $class->string = $string;
                    $class->int = $int;
                    $class->float = $float;

                    return $class;
                })
                ->mapper()
                ->map(stdClass::class, [
                    'string' => 'foo',
                    'int' => 42,
                ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $result->string);
        self::assertSame(42, $result->int);
        self::assertSame(1337.404, $result->float);
    }

    public function test_registered_constructors_for_same_class_are_filtered_correctly(): void
    {
        $mapper = $this->mapperBuilder
            // Basic constructor
            ->registerConstructor(function (string $foo): stdClass {
                $class = new stdClass();
                $class->foo = $foo;

                return $class;
            })
            // Constructor with two parameters
            ->registerConstructor(function (string $foo, int $bar): stdClass {
                $class = new stdClass();
                $class->foo = $foo;
                $class->bar = $bar;

                return $class;
            })
            // Constructor with optional parameter
            ->registerConstructor(function (string $foo, int $bar, float $baz, string $fiz = 'fiz'): stdClass {
                $class = new stdClass();
                $class->foo = $foo;
                $class->bar = $bar;
                $class->baz = $baz;
                $class->fiz = $fiz;

                return $class;
            })
            ->mapper();

        try {
            $resultA = $mapper->map(stdClass::class, [
                'foo' => 'foo',
            ]);

            $resultB = $mapper->map(stdClass::class, [
                'foo' => 'foo',
                'bar' => 42,
            ]);

            $resultC = $mapper->map(stdClass::class, [
                'foo' => 'foo',
                'bar' => 42,
                'baz' => 1337.404,
            ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $resultA->foo);

        self::assertSame('foo', $resultB->foo);
        self::assertSame(42, $resultB->bar);

        self::assertSame('foo', $resultC->foo);
        self::assertSame(42, $resultC->bar);
        self::assertSame(1337.404, $resultC->baz);
        self::assertSame('fiz', $resultC->fiz);
    }

    public function test_identical_registered_constructors_throws_exception(): void
    {
        try {
            $this->mapperBuilder
                ->registerConstructor(
                    fn (): stdClass => new stdClass(),
                    fn (): stdClass => new stdClass(),
                )
                ->mapper()
                ->map(stdClass::class, []);
        } catch (MappingError $exception) {
            $error = $exception->node()->messages()[0];

            self::assertSame('1642787246', $error->code());
            self::assertSame('Invalid value array (empty).', (string)$error);
        }
    }

    public function test_source_not_matching_registered_constructors_throws_exception(): void
    {
        try {
            $this->mapperBuilder
                ->registerConstructor(
                    fn (int $bar, float $baz = 1337.404): stdClass => new stdClass(),
                    fn (string $foo): stdClass => new stdClass(),
                )
                ->mapper()
                ->map(stdClass::class, []);
        } catch (MappingError $exception) {
            $error = $exception->node()->messages()[0];

            self::assertSame('1642183169', $error->code());
            self::assertSame('Value array (empty) does not match any of `array{foo: string}`, `array{bar: int, baz?: float}`.', (string)$error);
        }
    }

    public function test_non_registered_named_constructors_are_ignored(): void
    {
        $this->expectException(CannotInstantiateObject::class);
        $this->expectExceptionCode(1646916477);
        $this->expectExceptionMessage('No available constructor found for class `' . SomeClassWithPrivateNativeConstructor::class . '`');

        $this->mapperBuilder
            ->mapper()
            ->map(SomeClassWithPrivateNativeConstructor::class, []);
    }

    public function test_registered_datetime_constructor_is_used(): void
    {
        $default = new DateTime('@1356097062');
        $defaultImmutable = new DateTimeImmutable('@1356097062');

        $mapper = $this->mapperBuilder
            ->registerConstructor(fn (int $timestamp): DateTime => $default)
            ->registerConstructor(fn (int $timestamp): DateTimeImmutable => $defaultImmutable)
            ->mapper();

        try {
            $result = $mapper->map(DateTime::class, 1357047105);
            $resultImmutable = $mapper->map(DateTimeImmutable::class, 1357047105);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame($default, $result);
        self::assertSame($defaultImmutable, $resultImmutable);
    }

    public function test_registered_datetime_constructor_not_matching_source_uses_default_constructor(): void
    {
        try {
            $result = $this->mapperBuilder
                ->registerConstructor(fn (string $foo, int $bar): DateTimeInterface => new DateTimeImmutable())
                ->mapper()
                ->map(DateTimeInterface::class, 1647781015);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertEquals(new DateTimeImmutable('@1647781015'), $result);
    }

    public function test_registered_constructor_throwing_exception_fails_mapping_with_message(): void
    {
        try {
            $this->mapperBuilder
                ->registerConstructor(function (): stdClass {
                    // @PHP8.0 use short closure
                    throw new DomainException('some domain exception');
                })
                ->mapper()
                ->map(stdClass::class, []);

            self::fail('No mapping error when one was expected');
        } catch (MappingError $exception) {
            self::assertSame('some domain exception', (string)$exception->node()->messages()[0]);
        }
    }

    public function test_deprecated_bind_function_to_registered_constructor_is_used(): void
    {
        $object = new stdClass();

        try {
            $result = $this->mapperBuilder
                ->bind(fn (): stdClass => $object)
                ->mapper()
                ->map(stdClass::class, []);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame($object, $result);
    }
}

final class SomeClassWithNamedConstructors
{
    public string $foo;

    public static function namedConstructor(string $foo): self
    {
        $instance = new self();
        $instance->foo = $foo;

        return $instance;
    }
}

final class SomeClassWithSimilarNativeConstructorAndNamedConstructor
{
    public string $foo;

    public function __construct(string $foo)
    {
        $this->foo = $foo;
    }

    public static function namedConstructor(string $foo): self
    {
        $instance = new self($foo);
        $instance->foo = 'value from named constructor';

        return $instance;
    }
}

final class SomeClassWithDifferentNativeConstructorAndNamedConstructor
{
    public string $foo;

    public int $bar;

    public function __construct(string $foo, int $bar)
    {
        $this->foo = $foo;
        $this->bar = $bar;
    }

    public static function namedConstructor(string $foo): self
    {
        return new self($foo, 42);
    }
}

final class SomeClassWithPrivateNativeConstructor
{
    public string $foo;

    private function __construct(string $foo)
    {
        $this->foo = $foo;
    }

    public static function namedConstructorWithNoParameter(): self
    {
        return new self('foo');
    }

    public static function namedConstructorWithParameter(string $foo): self
    {
        return new self($foo);
    }
}
