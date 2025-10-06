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
use CuyZ\Valinor\Mapper\Tree\Exception\InterfaceHasBothConstructorAndInfer;
use CuyZ\Valinor\Tests\Fake\Mapper\Tree\Message\FakeErrorMessage;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;
use CuyZ\Valinor\Tests\Integration\Mapping\Fixture\SimpleObject;
use CuyZ\Valinor\Tests\Integration\Mapping\Fixture\SimpleObjectWithGeneric;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use stdClass;

final class ConstructorRegistrationMappingTest extends IntegrationTestCase
{
    public function test_registered_anonymous_function_constructor_is_used(): void
    {
        $object = new stdClass();

        try {
            $result = $this->mapperBuilder()
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
            $result = $this->mapperBuilder()
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
            $result = $this->mapperBuilder()
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
            $result = $this->mapperBuilder()
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
            $result = $this->mapperBuilder()
                ->registerConstructor(SomeClassWithNamedConstructors::namedConstructor(...))
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
            $result = $this->mapperBuilder()
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
            $result = $this->mapperBuilder()
                ->registerConstructor($constructor->build(...))
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
            $result = $this->mapperBuilder()
                ->registerConstructor(SomeClassWithStaticConstructorForOtherClass::from(...))
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
            $result = $this->mapperBuilder()
                ->registerConstructor(
                    /**
                     * @param class-string<SomeAbstractClassWithStaticConstructor> $className
                     */
                    #[DynamicConstructor]
                    // @phpstan-ignore return.type (we cannot set closure parameters / see https://github.com/phpstan/phpstan/issues/3770)
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
            $result = $this->mapperBuilder()
                ->registerConstructor(
                    /**
                     * @param class-string<SomeInterfaceWithStaticConstructor> $className
                     */
                    #[DynamicConstructor]
                    // @phpstan-ignore return.type (we cannot set closure parameters / see https://github.com/phpstan/phpstan/issues/3770)
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

            $result = $this->mapperBuilder()
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

            $result = $this->mapperBuilder()
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
            $result = $this->mapperBuilder()
                ->registerConstructor(SomeClassWithSimilarNativeConstructorAndNamedConstructor::namedConstructor(...))
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
            $result = $this->mapperBuilder()
                ->registerConstructor(SomeClassWithDifferentNativeConstructorAndNamedConstructor::class)
                ->registerConstructor(SomeClassWithDifferentNativeConstructorAndNamedConstructor::namedConstructor(...))
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

    public function test_registered_constructor_with_one_argument_is_used(): void
    {
        try {
            $result = $this->mapperBuilder()
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
            $result = $this->mapperBuilder()
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

    public function test_inherited_static_constructor_is_used_to_map_child_class(): void
    {
        $class = (new class () {
            public SomeClassWithInheritedStaticConstructor $someChild;

            public SomeOtherClassWithInheritedStaticConstructor $someOtherChild;
        })::class;

        try {
            $result = $this->mapperBuilder()
                ->registerConstructor(SomeAbstractClassWithStaticConstructor::from(...))
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

    /**
     * @param list<callable> $constructors
     * @param array<array{value: mixed, expected: mixed}> $data
     */
    #[DataProvider('constructors_are_sorted_and_filtered_correctly_data_provider')]
    public function test_constructors_are_sorted_and_filtered_correctly(array $constructors, array $data): void
    {
        $mapperBuilder = $this->mapperBuilder()->registerConstructor(...$constructors);

        try {
            foreach ($data as $value) {
                $result = $mapperBuilder->mapper()->map(stdClass::class, $value['value']);

                self::assertSame($value['expected'], $result);

                // Also testing with allowed superfluous keys to be sure that
                // constructors with fewer arguments are taken into account but
                //filtered correctly.
                $result = $mapperBuilder->allowSuperfluousKeys()->mapper()->map(stdClass::class, $value['value']);

                self::assertSame($value['expected'], $result);
            }
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }
    }

    public static function constructors_are_sorted_and_filtered_correctly_data_provider(): iterable
    {
        $resultA = new stdClass();
        $resultB = new stdClass();
        $resultC = new stdClass();

        yield 'constructor is used when surrounded by other constructors' => [
            'constructors' => [
                fn (): DateTime => new DateTime(),
                // This constructor is surrounded by other ones to ensure it is
                // still used correctly.
                fn (): stdClass => $resultA,
                fn (): DateTimeImmutable => new DateTimeImmutable(),
            ],
            'data' => [
                [
                    'value' => [],
                    'expected' => $resultA,
                ],
            ],
        ];

        yield 'constructors for same class are sorted properly' => [
            'constructors' => [
                // Basic constructor
                fn (string $foo): stdClass => $resultA,
                // Constructor with two parameters
                fn (string $foo, int $bar): stdClass => $resultB,
                // Constructor with optional parameter
                fn (string $foo, int $bar, float $baz, string $fiz = 'fiz'): stdClass => $resultC,
            ],
            'data' => [
                'string source' => [
                    'value' => 'foo',
                    'expected' => $resultA,
                ],
                'foo and bar values' => [
                    'value' => [
                        'foo' => 'foo',
                        'bar' => 42,
                    ],
                    'expected' => $resultB,
                ],
                'foo and bar and baz values' => [
                    'value' => [
                        'foo' => 'foo',
                        'bar' => 42,
                        'baz' => 1337.0,
                    ],
                    'expected' => $resultC,
                ],
            ],
        ];

        yield 'constructors for same class with same arguments number but different types' => [
            'constructors' => [
                fn (string $foo, string $bar): stdClass => $resultA,
                fn (string $foo, string $fiz): stdClass => $resultB,
            ],
            'data' => [
                'foo and bar' => [
                    'value' => [
                        'foo' => 'foo',
                        'bar' => 'bar',
                    ],
                    'expected' => $resultA,
                ],
                'foo and fiz' => [
                    'value' => [
                        'foo' => 'foo',
                        'fiz' => 'fiz',
                    ],
                    'expected' => $resultB,
                ],
            ],
        ];

        yield 'constructors with same parameter name but different types' => [
            'constructors' => [
                fn (string $value): stdClass => $resultA,
                fn (float $value): stdClass => $resultB,
                fn (int $value): stdClass => $resultC,
            ],
            'data' => [
                'string source' => [
                    'value' => 'foo',
                    'expected' => $resultA,
                ],
                'float source' => [
                    'value' => 404.0,
                    'expected' => $resultB,
                ],
                'integer source' => [
                    'value' => 1337,
                    'expected' => $resultC,
                ],
            ],
        ];

        yield 'constructors with same named parameter use integer over float' => [
            'constructors' => [
                fn (float $value): stdClass => $resultA,
                fn (int $value): stdClass => $resultB,
            ],
            'data' => [
                [
                    'value' => 1337,
                    'expected' => $resultB,
                ],
            ],
        ];

        yield 'constructors with same named parameters names use integer over float' => [
            'constructors' => [
                fn (float $valueA, float $valueB): stdClass => $resultA,
                fn (int $valueA, int $valueB): stdClass => $resultB,
            ],
            'data' => [
                [
                    'value' => [
                        'valueA' => 42,
                        'valueB' => 1337,
                    ],
                    'expected' => $resultB,
                ],
            ],
        ];

        yield 'constructors with same parameter name but second one is either float or integer' => [
            'constructors' => [
                fn (int $valueA, float $valueB): stdClass => $resultA,
                fn (int $valueA, int $valueB): stdClass => $resultB,
            ],
            'data' => [
                'integer and float' => [
                    'value' => [
                        'valueA' => 42,
                        'valueB' => 1337.0,
                    ],
                    'expected' => $resultA,
                ],
                'integer and integer' => [
                    'value' => [
                        'valueA' => 42,
                        'valueB' => 1337,
                    ],
                    'expected' => $resultB,
                ],
            ],
        ];

        yield 'constructor with non scalar argument has priority over those with scalar (non scalar constructor is registered first)' => [
            'constructors' => [
                fn (int $valueA, SimpleObject $valueB): stdClass => $resultA,
                fn (int $valueA, string $valueB): stdClass => $resultB,
            ],
            'data' => [
                [
                    'value' => [
                        'valueA' => 42,
                        'valueB' => 'foo',
                    ],
                    'expected' => $resultA,
                ],
            ],
        ];

        yield 'constructor with non scalar argument has priority over those with scalar (non scalar constructor is registered last)' => [
            'constructors' => [
                fn (int $valueA, string $valueB): stdClass => $resultA,
                fn (int $valueA, SimpleObject $valueB): stdClass => $resultB,
            ],
            'data' => [
                [
                    'value' => [
                        'valueA' => 42,
                        'valueB' => 'foo',
                    ],
                    'expected' => $resultB,
                ],
            ],
        ];
    }

    public function test_identical_registered_constructors_with_no_argument_throws_exception(): void
    {
        $this->expectException(ObjectBuildersCollision::class);
        $this->expectExceptionMessageMatches('/A type collision was detected between the constructors `Closure .*` and `Closure .*`\./');

        $this->mapperBuilder()
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
        $this->expectExceptionMessageMatches('/A type collision was detected between the constructors `Closure .*` and `Closure .*`\./');

        $this->mapperBuilder()
            ->registerConstructor(
                fn (int $int): stdClass => new stdClass(),
                fn (int $int): stdClass => new stdClass(),
            )
            ->mapper()
            ->map(stdClass::class, []);
    }

    public function test_constructors_with_colliding_arguments_throws_exception(): void
    {
        $this->expectException(ObjectBuildersCollision::class);
        $this->expectExceptionMessageMatches('/A type collision was detected between the constructors `Closure .*` and `Closure .*`\./');

        $this->mapperBuilder()
            ->registerConstructor(
                fn (int $valueA, float $valueB): stdClass => new stdClass(),
                fn (float $valueA, int $valueB): stdClass => new stdClass(),
            )
            ->mapper()
            ->map(stdClass::class, []);
    }

    public function test_identical_registered_constructors_with_several_argument_throws_exception(): void
    {
        $this->expectException(ObjectBuildersCollision::class);
        $this->expectExceptionMessage('A type collision was detected between the constructors `CuyZ\Valinor\Tests\Integration\Mapping\constructorA()` and `CuyZ\Valinor\Tests\Integration\Mapping\constructorB()`.');

        $this->mapperBuilder()
            ->registerConstructor(
                constructorA(...),
                fn (int $other, float $arguments): stdClass => new stdClass(),
                constructorB(...),
            )
            ->mapper()
            ->map(stdClass::class, []);
    }

    public function test_non_intersecting_hashmap_type_constructors_do_not_lead_to_collisions(): void
    {
        $mapper = $this->mapperBuilder()
            ->registerConstructor(
                /** @param array{key: SimpleObject} $input */
                static fn (array $input): stdClass => (object)['single-item' => $input],
                /** @param array{key: list<SimpleObject>} $input */
                static fn (array $input): stdClass => (object)['multiple-items' => $input],
            )
            ->mapper();

        $hello = new SimpleObject();
        $world = new SimpleObject();

        $hello->value = 'hello';
        $world->value = 'world';

        try {
            self::assertEquals(
                (object) ['multiple-items' => ['key' => [$hello, $world]]],
                $mapper->map(stdClass::class, ['key' => ['hello', 'world']])
            );
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }
    }

    public function test_source_not_matching_registered_constructors_throws_exception(): void
    {
        try {
            $this->mapperBuilder()
                ->registerConstructor(
                    fn (int $bar, float $baz = 1337.404): stdClass => new stdClass(),
                    fn (string $foo): stdClass => new stdClass(),
                )
                ->mapper()
                ->map(stdClass::class, []);
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, [
                '*root*' => "[cannot_find_object_builder] Value array (empty) does not match `string|array{foo: string}|array{bar: int, baz?: float}`.",
            ]);
        }
    }

    public function test_non_registered_named_constructors_are_ignored(): void
    {
        $this->expectException(CannotInstantiateObject::class);
        $this->expectExceptionMessage('No available constructor found for class `' . SomeClassWithPrivateNativeConstructor::class . '`');

        $this->mapperBuilder()
            ->mapper()
            ->map(SomeClassWithPrivateNativeConstructor::class, []);
    }

    public function test_invalid_constructor_return_type_throws_exception(): void
    {
        $this->expectException(InvalidConstructorReturnType::class);
        $this->expectExceptionMessageMatches('/Invalid return type `string` for constructor `.*`\, it must be a valid class name\./');

        $this->mapperBuilder()
            ->registerConstructor(fn (): string => 'foo')
            ->mapper()
            ->map(stdClass::class, []);
    }

    public function test_invalid_constructor_return_type_missing_generic_throws_exception(): void
    {
        $this->expectException(InvalidConstructorReturnType::class);
        $this->expectExceptionMessageMatches('/The return type `.*` of function `.*` could not be resolved: No generic was assigned to the template\(s\) `T` for the class .*/');

        $this->mapperBuilder()
            ->registerConstructor(
                fn (): SimpleObjectWithGeneric => new SimpleObjectWithGeneric()
            )
            ->mapper()
            ->map(stdClass::class, []);
    }

    public function test_missing_constructor_class_type_parameter_throws_exception(): void
    {
        $this->expectException(MissingConstructorClassTypeParameter::class);
        $this->expectExceptionMessageMatches('/Missing first parameter of type `class-string` for the constructor `.*`\./');

        $this->mapperBuilder()
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
        $this->expectExceptionMessageMatches('/Invalid type `int` for the first parameter of the constructor `.*`, it should be of type `class-string`\./');

        $this->mapperBuilder()
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

        $mapper = $this->mapperBuilder()
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

        $mapper = $this->mapperBuilder()
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
            $result = $this->mapperBuilder()
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
            $this->mapperBuilder()
                ->registerConstructor(fn (): stdClass => throw new FakeErrorMessage('some error message', 1656076090))
                ->mapper()
                ->map(stdClass::class, []);

            self::fail('No mapping error when one was expected');
        } catch (MappingError $exception) {
            self::assertMappingErrors(
                $exception,
                [
                    '*root*' => "[1656076090] some error message",
                ],
                assertErrorsBodiesAreRegistered: false,
            );
        }
    }

    public function test_two_registered_constructors_for_interface_work_properly(): void
    {
        $mapper = $this
            ->mapperBuilder()
            ->registerConstructor(
                fn (string $foo, int $bar): SomeInterfaceWithRegisteredConstructor => new SomeClassImplementingInterfaceWithRegisteredConstructor($foo, $bar),
                fn (string $bar, int $baz): SomeInterfaceWithRegisteredConstructor => new SomeOtherClassImplementingInterfaceWithRegisteredConstructor($bar, $baz),
            )
            ->mapper();

        try {
            $resultA = $mapper->map(SomeInterfaceWithRegisteredConstructor::class, [
                'foo' => 'foo',
                'bar' => 42,
            ]);

            $resultB = $mapper->map(SomeInterfaceWithRegisteredConstructor::class, [
                'bar' => 'bar',
                'baz' => 1337,
            ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertInstanceOf(SomeClassImplementingInterfaceWithRegisteredConstructor::class, $resultA);
        self::assertSame('foo', $resultA->foo);
        self::assertSame(42, $resultA->bar);

        self::assertInstanceOf(SomeOtherClassImplementingInterfaceWithRegisteredConstructor::class, $resultB);
        self::assertSame('bar', $resultB->bar);
        self::assertSame(1337, $resultB->baz);
    }

    public function test_interface_with_both_constructor_and_infer_configurations_throws_exception(): void
    {
        $this->expectException(InterfaceHasBothConstructorAndInfer::class);
        $this->expectExceptionMessage('Interface `' . SomeInterfaceWithRegisteredConstructor::class . '` is configured with at least one constructor but also has an infer configuration. Only one method can be used.');

        $this->mapperBuilder()
            ->registerConstructor(
                fn (): SomeInterfaceWithRegisteredConstructor => new SomeClassImplementingInterfaceWithRegisteredConstructor('foo', 42)
            )
            ->infer(SomeInterfaceWithRegisteredConstructor::class, fn () => SomeClassImplementingInterfaceWithRegisteredConstructor::class)
            ->mapper()
            ->map(SomeInterfaceWithRegisteredConstructor::class, []);
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
        $object->value = $value;

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

interface SomeInterfaceWithRegisteredConstructor {}

final class SomeClassImplementingInterfaceWithRegisteredConstructor implements SomeInterfaceWithRegisteredConstructor
{
    public function __construct(
        public string $foo,
        public int $bar
    ) {}
}

final class SomeOtherClassImplementingInterfaceWithRegisteredConstructor implements SomeInterfaceWithRegisteredConstructor
{
    public function __construct(
        public string $bar,
        public int $baz
    ) {}
}
