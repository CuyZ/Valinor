<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Tree\Exception\CannotResolveObjectType;
use CuyZ\Valinor\Mapper\Tree\Exception\InvalidResolvedImplementationValue;
use CuyZ\Valinor\Mapper\Tree\Exception\MissingObjectImplementationRegistration;
use CuyZ\Valinor\Mapper\Tree\Exception\ObjectImplementationCallbackError;
use CuyZ\Valinor\Mapper\Tree\Exception\ObjectImplementationNotRegistered;
use CuyZ\Valinor\Mapper\Tree\Exception\ResolvedImplementationIsNotAccepted;
use CuyZ\Valinor\Tests\Fixture\Object\InterfaceWithDifferentNamespaces\A\ClassThatInheritsInterfaceA;
use CuyZ\Valinor\Tests\Fixture\Object\InterfaceWithDifferentNamespaces\B\ClassThatInheritsInterfaceB;
use CuyZ\Valinor\Tests\Fixture\Object\InterfaceWithDifferentNamespaces\ClassWithBothInterfaces;
use CuyZ\Valinor\Tests\Fixture\Object\InterfaceWithDifferentNamespaces\InterfaceA;
use CuyZ\Valinor\Tests\Fixture\Object\InterfaceWithDifferentNamespaces\InterfaceAInferer;
use CuyZ\Valinor\Tests\Fixture\Object\InterfaceWithDifferentNamespaces\InterfaceB;
use CuyZ\Valinor\Tests\Fixture\Object\InterfaceWithDifferentNamespaces\InterfaceBInferer;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;
use DateTime;
use DateTimeInterface;
use DomainException;
use stdClass;

final class InterfaceInferringMappingTest extends IntegrationTestCase
{
    public function test_override_date_time_interface_inferring_overrides_correctly(): void
    {
        try {
            $result = $this->mapperBuilder()
                ->infer(DateTimeInterface::class, fn () => DateTime::class)
                ->mapper()
                ->map(DateTimeInterface::class, 1645279176);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertInstanceOf(DateTime::class, $result);
        self::assertSame('1645279176', $result->format('U'));
    }

    public function test_single_argument_for_both_infer_and_object_constructor_can_be_used(): void
    {
        try {
            $mapper = $this->mapperBuilder()
                ->infer(
                    SomeInterface::class,
                    /** @return class-string<SomeClassThatInheritsInterfaceA>|class-string<SomeClassThatInheritsInterfaceB> */
                    fn (string $value) => match ($value) {
                        'fooA' => SomeClassThatInheritsInterfaceA::class,
                        default => SomeClassThatInheritsInterfaceB::class,
                    }
                )->mapper();

            $resultA = $mapper->map(SomeInterface::class, 'fooA');
            $resultB = $mapper->map(SomeInterface::class, 'fooB');
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertInstanceOf(SomeClassThatInheritsInterfaceA::class, $resultA);
        self::assertSame('fooA', $resultA->valueA);
        self::assertInstanceOf(SomeClassThatInheritsInterfaceB::class, $resultB);
        self::assertSame('fooB', $resultB->valueB);
    }

    public function test_infer_interface_with_union_of_class_string_works_properly(): void
    {
        try {
            $result = $this->mapperBuilder()
                ->infer(
                    SomeInterface::class,
                    /** @return class-string<SomeClassThatInheritsInterfaceA>|class-string<SomeClassThatInheritsInterfaceB> */
                    fn (): string => SomeClassThatInheritsInterfaceA::class
                )
                ->mapper()
                ->map(SomeInterface::class, 'foo');
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertInstanceOf(SomeClassThatInheritsInterfaceA::class, $result);
    }

    public function test_infer_interface_with_class_string_with_union_of_class_names_works_properly(): void
    {
        try {
            $result = $this->mapperBuilder()
                ->infer(
                    SomeInterface::class,
                    /** @return class-string<SomeClassThatInheritsInterfaceA|SomeClassThatInheritsInterfaceB> */
                    fn (): string => SomeClassThatInheritsInterfaceA::class
                )
                ->mapper()
                ->map(SomeInterface::class, 'foo');
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertInstanceOf(SomeClassThatInheritsInterfaceA::class, $result);
    }

    public function test_infer_interface_with_single_argument_works_properly(): void
    {
        try {
            [$resultA, $resultB] = $this->mapperBuilder()
                ->infer(
                    SomeInterface::class,
                    /** @return class-string<SomeClassThatInheritsInterfaceA|SomeClassThatInheritsInterfaceB> */
                    fn (string $type): string => match ($type) {
                        'classA-foo' => SomeClassThatInheritsInterfaceA::class,
                        'classB-bar' => SomeClassThatInheritsInterfaceB::class,
                        default => self::fail("Type `$type` not handled."),
                    }
                )
                ->mapper()
                ->map('list<' . SomeInterface::class . '>', [
                    'classA-foo',
                    ['type' => 'classB-bar', 'valueB' => 'classB-bar'],
                ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertInstanceOf(SomeClassThatInheritsInterfaceA::class, $resultA);
        self::assertInstanceOf(SomeClassThatInheritsInterfaceB::class, $resultB);
        self::assertSame('classA-foo', $resultA->valueA);
        self::assertSame('classB-bar', $resultB->valueB);
    }

    public function test_infer_interface_with_several_arguments_works_properly(): void
    {
        try {
            [$resultA, $resultB] = $this->mapperBuilder()
                ->infer(
                    SomeInterface::class,
                    /** @return class-string<SomeClassThatInheritsInterfaceA|SomeClassThatInheritsInterfaceB> */
                    function (string $type, int $key): string {
                        if ($type === 'classA' && $key === 42) {
                            return SomeClassThatInheritsInterfaceA::class;
                        } elseif ($type === 'classB' && $key === 1337) {
                            return SomeClassThatInheritsInterfaceB::class;
                        }

                        self::fail("Combination `$type` / `$key` not handled.");
                    }
                )
                ->mapper()
                ->map('list<' . SomeInterface::class . '>', [
                    [
                        'type' => 'classA',
                        'key' => 42,
                        'valueA' => 'foo',
                    ],
                    [
                        'type' => 'classB',
                        'key' => 1337,
                        'valueB' => 'bar',
                    ],
                ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertInstanceOf(SomeClassThatInheritsInterfaceA::class, $resultA);
        self::assertInstanceOf(SomeClassThatInheritsInterfaceB::class, $resultB);
        self::assertSame('foo', $resultA->valueA);
        self::assertSame('bar', $resultB->valueB);
    }

    public function test_infer_with_two_functions_with_same_name_works_properly(): void
    {
        try {
            $result = $this->mapperBuilder()
                ->infer(
                    InterfaceA::class,
                    InterfaceAInferer::infer(...)
                )
                ->infer(
                    InterfaceB::class,
                    InterfaceBInferer::infer(...)
                )
                ->mapper()
                ->map(ClassWithBothInterfaces::class, [
                    'a' => [
                        'classic' => true,
                        'value' => 'foo',
                    ],
                    'b' => [
                        'classic' => true,
                        'value' => 'bar',
                    ],
                ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertInstanceOf(ClassThatInheritsInterfaceA::class, $result->a);
        self::assertInstanceOf(ClassThatInheritsInterfaceB::class, $result->b);
    }

    public function test_unresolvable_implementation_throws_exception(): void
    {
        $this->expectException(CannotResolveObjectType::class);
        $this->expectExceptionCode(1618049116);
        $this->expectExceptionMessage('Impossible to resolve an implementation for `' . SomeInterface::class . '`.');

        $this->mapperBuilder()
            ->mapper()
            ->map(SomeInterface::class, []);
    }

    public function test_invalid_resolved_implementation_value_throws_exception(): void
    {
        $this->expectException(InvalidResolvedImplementationValue::class);
        $this->expectExceptionCode(1630091260);
        $this->expectExceptionMessage('Invalid value 42, expected a subtype of `DateTimeInterface`.');

        $this->mapperBuilder()
            ->infer(DateTimeInterface::class, fn () => 42)
            ->mapper()
            ->map(DateTimeInterface::class, []);
    }

    public function test_invalid_resolved_implementation_type_throws_exception(): void
    {
        $this->expectException(ResolvedImplementationIsNotAccepted::class);
        $this->expectExceptionCode(1618049487);
        $this->expectExceptionMessage('Invalid implementation type `int`, expected a subtype of `DateTimeInterface`.');

        $this->mapperBuilder()
            ->infer(DateTimeInterface::class, fn () => 'int')
            ->mapper()
            ->map(DateTimeInterface::class, []);
    }

    public function test_invalid_resolved_implementation_throws_exception(): void
    {
        $this->expectException(ResolvedImplementationIsNotAccepted::class);
        $this->expectExceptionCode(1618049487);
        $this->expectExceptionMessage('Invalid implementation type `stdClass`, expected a subtype of `DateTimeInterface`.');

        $this->mapperBuilder()
            ->infer(DateTimeInterface::class, fn () => stdClass::class)
            ->mapper()
            ->map(DateTimeInterface::class, []);
    }

    public function test_invalid_object_type_resolved_implementation_throws_exception(): void
    {
        $this->expectException(ResolvedImplementationIsNotAccepted::class);
        $this->expectExceptionCode(1618049487);
        $this->expectExceptionMessage('Invalid implementation type `DateTimeInterface`, expected a subtype of `DateTimeInterface`.');

        $this->mapperBuilder()
            ->infer(DateTimeInterface::class, fn () => DateTimeInterface::class)
            ->mapper()
            ->map(DateTimeInterface::class, []);
    }

    public function test_object_implementation_callback_error_throws_exception(): void
    {
        $exception = new DomainException('some error message', 1653990051);

        $this->expectException(ObjectImplementationCallbackError::class);
        $this->expectExceptionCode(1653983061);
        $this->expectExceptionMessage('Error thrown when trying to get implementation of `DateTimeInterface`: some error message');

        $this->mapperBuilder()
            ->infer(
                DateTimeInterface::class,
                fn () => throw $exception
            )
            ->mapper()
            ->map(DateTimeInterface::class, []);
    }

    public function test_missing_object_implementation_registration_throws_exception(): void
    {
        $this->expectException(MissingObjectImplementationRegistration::class);
        $this->expectExceptionCode(1653990549);
        $this->expectExceptionMessage('No implementation of `' . SomeInterface::class . '` found with return type `mixed` of');

        $this->mapperBuilder()
            ->infer(
                SomeInterface::class,
                fn (string $type) => SomeClassThatInheritsInterfaceA::class
            )
            ->mapper()
            ->map(SomeInterface::class, 'foo');
    }

    public function test_invalid_union_object_implementation_registration_throws_exception(): void
    {
        $this->expectException(MissingObjectImplementationRegistration::class);
        $this->expectExceptionCode(1653990549);
        $this->expectExceptionMessage('No implementation of `' . SomeInterface::class . '` found with return type `string|int` of');

        $this->mapperBuilder()
            ->infer(
                SomeInterface::class,
                fn (string $value): string|int => $value === 'foo' ? 'foo' : 42
            )
            ->mapper()
            ->map(SomeInterface::class, 'foo');
    }

    public function test_invalid_class_string_object_implementation_registration_throws_exception(): void
    {
        $this->expectException(MissingObjectImplementationRegistration::class);
        $this->expectExceptionCode(1653990549);
        $this->expectExceptionMessage('No implementation of `' . SomeInterface::class . '` found with return type `class-string` of');

        $this->mapperBuilder()
            ->infer(
                SomeInterface::class,
                /** @return class-string */
                fn () => SomeClassThatInheritsInterfaceA::class
            )
            ->mapper()
            ->map(SomeInterface::class, []);
    }

    public function test_object_implementation_not_registered_throws_exception(): void
    {
        $this->expectException(ObjectImplementationNotRegistered::class);
        $this->expectExceptionCode(1653990989);
        $this->expectExceptionMessage('Invalid implementation `' . SomeClassThatInheritsInterfaceC::class . '` for `' . SomeInterface::class . '`, it should be one of `' . SomeClassThatInheritsInterfaceA::class . '`, `' . SomeClassThatInheritsInterfaceB::class . '`.');

        $this->mapperBuilder()
            ->infer(
                SomeInterface::class,
                /** @return class-string<SomeClassThatInheritsInterfaceA|SomeClassThatInheritsInterfaceB> */
                fn (): string => SomeClassThatInheritsInterfaceC::class
            )
            ->mapper()
            ->map(SomeInterface::class, []);
    }

    public function test_invalid_source_throws_exception(): void
    {
        try {
            $this->mapperBuilder()
                ->infer(
                    SomeInterface::class,
                    /** @return class-string<SomeClassThatInheritsInterfaceA> */
                    fn (string $type, int $key) => SomeClassThatInheritsInterfaceA::class
                )
                ->mapper()
                ->map(SomeInterface::class, 42);
        } catch (MappingError $exception) {
            $error = $exception->node()->messages()[0];

            self::assertSame('1632903281', $error->code());
            self::assertSame('Value 42 does not match type `array{type: string, key: int}`.', (string)$error);
        }
    }

    public function test_invalid_source_value_throws_exception(): void
    {
        try {
            $this->mapperBuilder()
                ->infer(
                    SomeInterface::class,
                    /** @return class-string<SomeClassThatInheritsInterfaceA> */
                    fn (int $key) => SomeClassThatInheritsInterfaceA::class
                )
                ->mapper()
                ->map(SomeInterface::class, 'foo');
        } catch (MappingError $exception) {
            $error = $exception->node()->children()['key']->messages()[0];

            self::assertSame("Value 'foo' is not a valid integer.", (string)$error);
        }
    }

    public function test_superfluous_values_throws_exception(): void
    {
        try {
            $this->mapperBuilder()
                ->infer(
                    SomeInterface::class,
                    /** @return class-string<SomeClassThatInheritsInterfaceA> */
                    fn (string $valueA) => SomeClassThatInheritsInterfaceA::class
                )
                ->mapper()
                ->map(SomeInterface::class, [
                    'valueA' => 'foo',
                    'superfluousValue' => 'bar',
                ]);
        } catch (MappingError $exception) {
            $error = $exception->node()->messages()[0];

            self::assertSame('1655117782', $error->code());
            self::assertSame('Unexpected key(s) `superfluousValue`, expected `valueA`.', (string)$error);
        }
    }
}

interface SomeInterface {}

final class SomeClassThatInheritsInterfaceA implements SomeInterface
{
    public string $valueA;
}

final class SomeClassThatInheritsInterfaceB implements SomeInterface
{
    public string $valueB;
}

final class SomeClassThatInheritsInterfaceC implements SomeInterface {}
