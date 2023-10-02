<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping;

use Closure;
use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Object\DynamicConstructor;
use CuyZ\Valinor\Mapper\Object\Exception\CannotInstantiateObject;
use CuyZ\Valinor\Mapper\Object\Exception\InvalidConstructorClassTypeParameter;
use CuyZ\Valinor\Mapper\Object\Exception\InvalidConstructorReturnType;
use CuyZ\Valinor\Mapper\Object\Exception\MissingConstructorClassTypeParameter;
use CuyZ\Valinor\Mapper\Object\Exception\ObjectBuildersCollision;
use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Tests\Fake\Mapper\Tree\Message\FakeErrorMessage;
use CuyZ\Valinor\Tests\Integration\IntegrationTest;
use CuyZ\Valinor\Tests\Integration\Mapping\Fixture\SimpleObject;
use CuyZ\Valinor\Tests\Integration\Mapping\Fixture\SimpleObjectWithGeneric;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use stdClass;

final class ConstructorRegistrationMappingTest extends IntegrationTest
{
    public function test_registered_anonymous_function_constructor_is_used(): void
    {
        $object = new stdClass();

        try {
            $result = (new MapperBuilder())
                ->registerConstructor(fn (): stdClass => $object)
                ->mapper()
                ->map(stdClass::class, []);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame($object, $result);
    }

    public function test_registered_static_anonymous_function_constructor_is_used(): void
    {
        $object = new stdClass();

        try {
            $result = (new MapperBuilder())
                ->registerConstructor(static fn (): stdClass => $object)
                ->mapper()
                ->map(stdClass::class, []);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame($object, $result);
    }

    public function test_registered_anonymous_function_from_static_scope_constructor_is_used(): void
    {
        $object = new stdClass();

        try {
            $result = (new MapperBuilder())
                ->registerConstructor(SomeClassProvidingStaticClosure::getConstructor($object))
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

        try {
            $result = (new MapperBuilder())
                ->registerConstructor(/** @return stdClass */ fn () => $object)
                ->mapper()
                ->map(stdClass::class, []);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame($object, $result);
    }

    public function test_registered_named_constructor_is_used(): void
    {
        try {
            $result = (new MapperBuilder())
                // PHP8.1 first-class callable syntax
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
            $result = (new MapperBuilder())
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
            $result = (new MapperBuilder())
                // PHP8.1 first-class callable syntax
                ->registerConstructor([$constructor, 'build'])
                ->mapper()
                ->map(stdClass::class, []);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $result->foo);
    }

    public function test_class_static_constructor_for_other_class_is_used(): void
    {
        try {
            $result = (new MapperBuilder())
                // PHP8.1 first-class callable syntax
                ->registerConstructor([SomeClassWithStaticConstructorForOtherClass::class, 'from'])
                ->mapper()
                ->map(SimpleObject::class, 'foo');
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $result->value);
    }

    public function test_registered_constructor_with_injected_class_name_is_used_for_abstract_class(): void
    {
        try {
            $result = (new MapperBuilder())
                ->registerConstructor(
                    /**
                     * @param class-string<SomeAbstractClassWithStaticConstructor> $className
                     */
                    #[DynamicConstructor]
                    fn (string $className, string $foo, int $bar): SomeAbstractClassWithStaticConstructor => $className::from($foo, $bar)
                )
                ->mapper()
                ->map(SomeClassWithBothInheritedStaticConstructors::class, [
                    'someChild' => ['foo' => 'foo', 'bar' => 42],
                    'someOtherChild' => ['foo' => 'fiz', 'bar' => 1337],
                ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $result->someChild->foo);
        self::assertSame(42, $result->someChild->bar);
        self::assertSame('fiz', $result->someOtherChild->foo);
        self::assertSame(1337, $result->someOtherChild->bar);
    }

    public function test_registered_constructor_with_injected_class_name_is_used_for_interface(): void
    {
        try {
            $result = (new MapperBuilder())
                ->registerConstructor(
                    /**
                     * @param class-string<SomeInterfaceWithStaticConstructor> $className
                     */
                    #[DynamicConstructor]
                    fn (string $className, string $foo, int $bar): SomeInterfaceWithStaticConstructor => $className::from($foo, $bar)
                )
                ->mapper()
                ->map(SomeClassWithBothInheritedStaticConstructors::class, [
                    'someChild' => ['foo' => 'foo', 'bar' => 42],
                    'someOtherChild' => ['foo' => 'fiz', 'bar' => 1337],
                ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $result->someChild->foo);
        self::assertSame(42, $result->someChild->bar);
        self::assertSame('fiz', $result->someOtherChild->foo);
        self::assertSame(1337, $result->someOtherChild->bar);
    }

    public function test_registered_constructor_with_injected_class_name_without_class_string_type_is_used(): void
    {
        try {
            $object = new stdClass();

            $result = (new MapperBuilder())
                ->registerConstructor(
                    #[DynamicConstructor]
                    fn (string $className, string $foo): stdClass => $object
                )
                ->mapper()
                ->map(stdClass::class, 'foo');
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame($object, $result);
    }

    public function test_registered_constructor_with_injected_class_name_with_previously_other_registered_constructor_is_used(): void
    {
        try {
            $object = new stdClass();

            $result = (new MapperBuilder())
                ->registerConstructor(fn (): DateTimeInterface => new DateTime())
                ->registerConstructor(
                    #[DynamicConstructor]
                    fn (string $className, string $foo): stdClass => $object
                )
                ->mapper()
                ->map(stdClass::class, 'foo');
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame($object, $result);
    }

    public function test_native_constructor_is_not_called_if_not_registered_but_other_constructors_are_registered(): void
    {
        try {
            $result = (new MapperBuilder())
                // PHP8.1 first-class callable syntax
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
            $result = (new MapperBuilder())
                // PHP8.1 first-class callable syntax
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
            $result = (new MapperBuilder())
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
            $result = (new MapperBuilder())
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
            $result = (new MapperBuilder())
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
        $mapper = (new MapperBuilder())
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
            $resultA = $mapper->map(stdClass::class, 'foo');

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

    public function test_several_constructors_with_same_arguments_number_are_filtered_correctly(): void
    {
        $mapper = (new MapperBuilder())
            ->registerConstructor(function (string $foo, string $bar): stdClass {
                $class = new stdClass();
                $class->foo = $foo;
                $class->bar = $bar;

                return $class;
            })
            ->registerConstructor(function (string $foo, string $baz): stdClass {
                $class = new stdClass();
                $class->foo = $foo;
                $class->baz = $baz;

                return $class;
            })->mapper();

        try {
            $resultA = $mapper->map(stdClass::class, [
                'foo' => 'foo',
                'bar' => 'bar',
            ]);

            $resultB = $mapper->map(stdClass::class, [
                'foo' => 'foo',
                'baz' => 'baz',
            ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $resultA->foo);
        self::assertSame('bar', $resultA->bar);
        self::assertSame('foo', $resultB->foo);
        self::assertSame('baz', $resultB->baz);
    }

    public function test_inherited_static_constructor_is_used_to_map_child_class(): void
    {
        $class = (new class () {
            public SomeClassWithInheritedStaticConstructor $someChild;

            public SomeOtherClassWithInheritedStaticConstructor $someOtherChild;
        })::class;

        try {
            $result = (new MapperBuilder())
                // PHP8.1 First-class callable syntax
                ->registerConstructor([SomeAbstractClassWithStaticConstructor::class, 'from'])
                ->mapper()
                ->map($class, [
                    'someChild' => ['foo' => 'foo', 'bar' => 42],
                    'someOtherChild' => ['foo' => 'fiz', 'bar' => 1337],
                ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $result->someChild->foo);
        self::assertSame(42, $result->someChild->bar);
        self::assertSame('fiz', $result->someOtherChild->foo);
        self::assertSame(1337, $result->someOtherChild->bar);
    }

    public function test_identical_registered_constructors_with_no_argument_throws_exception(): void
    {
        $this->expectException(ObjectBuildersCollision::class);
        $this->expectExceptionCode(1654955787);
        $this->expectExceptionMessageMatches('/A collision was detected between the following constructors of the class `stdClass`: `Closure .*`, `Closure .*`\./');

        (new MapperBuilder())
            ->registerConstructor(
                fn (string $foo): stdClass => new stdClass(),
                fn (): stdClass => new stdClass(),
                fn (): stdClass => new stdClass(),
            )
            ->mapper()
            ->map(stdClass::class, []);
    }

    public function test_identical_registered_constructors_with_one_argument_throws_exception(): void
    {
        $this->expectException(ObjectBuildersCollision::class);
        $this->expectExceptionCode(1654955787);
        $this->expectExceptionMessageMatches('/A collision was detected between the following constructors of the class `stdClass`: `Closure .*`, `Closure .*`\./');

        (new MapperBuilder())
            ->registerConstructor(
                fn (int $int): stdClass => new stdClass(),
                fn (float $float): stdClass => new stdClass(),
            )
            ->mapper()
            ->map(stdClass::class, []);
    }

    public function test_identical_registered_constructors_with_several_argument_throws_exception(): void
    {
        $this->expectException(ObjectBuildersCollision::class);
        $this->expectExceptionCode(1654955787);
        $this->expectExceptionMessage('A collision was detected between the following constructors of the class `stdClass`: `CuyZ\Valinor\Tests\Integration\Mapping\constructorA()`, `CuyZ\Valinor\Tests\Integration\Mapping\constructorB()`.');

        (new MapperBuilder())
            ->registerConstructor(
                __NAMESPACE__ . '\constructorA', // PHP8.1 First-class callable syntax
                fn (int $other, float $arguments): stdClass => new stdClass(),
                __NAMESPACE__ . '\constructorB', // PHP8.1 First-class callable syntax
            )
            ->mapper()
            ->map(stdClass::class, []);
    }

    public function test_source_not_matching_registered_constructors_throws_exception(): void
    {
        try {
            (new MapperBuilder())
                ->registerConstructor(
                    fn (int $bar, float $baz = 1337.404): stdClass => new stdClass(),
                    fn (string $foo): stdClass => new stdClass(),
                )
                ->mapper()
                ->map(stdClass::class, []);
        } catch (MappingError $exception) {
            $error = $exception->node()->messages()[0];

            self::assertSame('1642183169', $error->code());
            self::assertSame('Value array (empty) does not match any of `string`, `array{bar: int, baz?: float}`.', (string)$error);
        }
    }

    public function test_non_registered_named_constructors_are_ignored(): void
    {
        $this->expectException(CannotInstantiateObject::class);
        $this->expectExceptionCode(1646916477);
        $this->expectExceptionMessage('No available constructor found for class `' . SomeClassWithPrivateNativeConstructor::class . '`');

        (new MapperBuilder())
            ->mapper()
            ->map(SomeClassWithPrivateNativeConstructor::class, []);
    }

    public function test_invalid_constructor_return_type_throws_exception(): void
    {
        $this->expectException(InvalidConstructorReturnType::class);
        $this->expectExceptionCode(1659446121);
        $this->expectExceptionMessageMatches('/Invalid return type `string` for constructor `.*`\, it must be a valid class name\./');

        (new MapperBuilder())
            ->registerConstructor(fn (): string => 'foo')
            ->mapper()
            ->map(stdClass::class, []);
    }

    public function test_invalid_constructor_return_type_missing_generic_throws_exception(): void
    {
        $this->expectException(InvalidConstructorReturnType::class);
        $this->expectExceptionCode(1659446121);
        $this->expectExceptionMessageMatches('/The type `.*` for return type of method `.*` could not be resolved: No generic was assigned to the template\(s\) `T` for the class .*/');

        (new MapperBuilder())
            ->registerConstructor(
                fn (): SimpleObjectWithGeneric => new SimpleObjectWithGeneric()
            )
            ->mapper()
            ->map(stdClass::class, []);
    }

    public function test_missing_constructor_class_type_parameter_throws_exception(): void
    {
        $this->expectException(MissingConstructorClassTypeParameter::class);
        $this->expectExceptionCode(1661516853);
        $this->expectExceptionMessageMatches('/Missing first parameter of type `class-string` for the constructor `.*`\./');

        (new MapperBuilder())
            ->registerConstructor(
                #[DynamicConstructor]
                fn (): stdClass => new stdClass()
            )
            ->mapper()
            ->map(stdClass::class, []);
    }

    public function test_invalid_constructor_class_type_parameter_throws_exception(): void
    {
        $this->expectException(InvalidConstructorClassTypeParameter::class);
        $this->expectExceptionCode(1661517000);
        $this->expectExceptionMessageMatches('/Invalid type `int` for the first parameter of the constructor `.*`, it should be of type `class-string`\./');

        (new MapperBuilder())
            ->registerConstructor(
                #[DynamicConstructor]
                fn (int $invalidParameterType): stdClass => new stdClass()
            )
            ->mapper()
            ->map(stdClass::class, []);
    }

    public function test_registered_datetime_constructor_is_used(): void
    {
        $default = new DateTime('@1356097062');
        $defaultImmutable = new DateTimeImmutable('@1356097062');

        $mapper = (new MapperBuilder())
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

    public function test_constructor_with_same_number_of_arguments_as_native_datetime_constructor_is_handled(): void
    {
        $default = new DateTime('@1659691266');
        $defaultImmutable = new DateTimeImmutable('@1659691266');

        $mapper = (new MapperBuilder())
            ->registerConstructor(fn (int $timestamp, string $timezone): DateTime => $default)
            ->registerConstructor(fn (int $timestamp, string $timezone): DateTimeImmutable => $defaultImmutable)
            ->mapper();

        try {
            $result = $mapper->map(DateTime::class, ['timestamp' => 1659704697, 'timezone' => 'Europe/Paris']);
            $resultImmutable = $mapper->map(DateTimeImmutable::class, ['timestamp' => 1659704697, 'timezone' => 'Europe/Paris']);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame($default, $result);
        self::assertSame($defaultImmutable, $resultImmutable);
    }

    public function test_registered_datetime_constructor_not_matching_source_uses_default_constructor(): void
    {
        try {
            $result = (new MapperBuilder())
                ->registerConstructor(fn (string $foo, int $bar): DateTimeImmutable => new DateTimeImmutable())
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
            (new MapperBuilder())
                // @phpstan-ignore-next-line
                ->registerConstructor(fn (): stdClass => throw new FakeErrorMessage('some error message', 1656076090))
                ->mapper()
                ->map(stdClass::class, []);

            self::fail('No mapping error when one was expected');
        } catch (MappingError $exception) {
            $error = $exception->node()->messages()[0];

            self::assertSame('1656076090', $error->code());
            self::assertSame('some error message', (string)$error);
        }
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
    public function __construct(public string $foo) {}

    public static function namedConstructor(string $foo): self
    {
        $instance = new self($foo);
        $instance->foo = 'value from named constructor';

        return $instance;
    }
}

final class SomeClassWithDifferentNativeConstructorAndNamedConstructor
{
    public function __construct(public string $foo, public int $bar) {}

    public static function namedConstructor(string $foo): self
    {
        return new self($foo, 42);
    }
}

final class SomeClassWithPrivateNativeConstructor
{
    private function __construct(public string $foo) {}

    public static function namedConstructorWithNoParameter(): self
    {
        return new self('foo');
    }

    public static function namedConstructorWithParameter(string $foo): self
    {
        return new self($foo);
    }
}

function constructorA(int $argumentA, float $argumentB): stdClass
{
    return new stdClass();
}

function constructorB(int $argumentA, float $argumentB): stdClass
{
    return new stdClass();
}

interface SomeInterfaceWithStaticConstructor
{
    public static function from(string $foo, int $bar): static;
}

abstract class SomeAbstractClassWithStaticConstructor implements SomeInterfaceWithStaticConstructor
{
    final private function __construct(public string $foo, public int $bar) {}

    public static function from(string $foo, int $bar): static
    {
        return new static($foo, $bar);
    }
}

final class SomeClassWithInheritedStaticConstructor extends SomeAbstractClassWithStaticConstructor {}

final class SomeOtherClassWithInheritedStaticConstructor extends SomeAbstractClassWithStaticConstructor {}

final class SomeClassWithBothInheritedStaticConstructors
{
    public SomeClassWithInheritedStaticConstructor $someChild;

    public SomeOtherClassWithInheritedStaticConstructor $someOtherChild;
}

final class SomeClassWithStaticConstructorForOtherClass
{
    public static function from(string $value): SimpleObject
    {
        $object = new SimpleObject();
        $object->value= $value;

        return $object;
    }
}

final class SomeClassProvidingStaticClosure
{
    public static function getConstructor(stdClass $object): Closure
    {
        return fn (): stdClass => $object;
    }
}
