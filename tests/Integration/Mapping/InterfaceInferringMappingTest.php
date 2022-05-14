<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Tree\Exception\InvalidInterfaceResolverReturnType;
use CuyZ\Valinor\Mapper\Tree\Exception\InvalidTypeResolvedForInterface;
use CuyZ\Valinor\Mapper\Tree\Exception\ResolvedTypeForInterfaceIsNotAccepted;
use CuyZ\Valinor\Tests\Integration\IntegrationTest;
use CuyZ\Valinor\Type\Resolver\Exception\CannotResolveTypeFromInterface;
use DateTime;
use DateTimeInterface;
use DomainException;
use stdClass;

final class InterfaceInferringMappingTest extends IntegrationTest
{
    public function test_override_date_time_interface_inferring_overrides_correctly(): void
    {
        try {
            $result = $this->mapperBuilder
                ->infer(DateTimeInterface::class, fn () => DateTime::class)
                ->mapper()
                ->map(DateTimeInterface::class, 1645279176);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertInstanceOf(DateTime::class, $result);
        self::assertSame('1645279176', $result->format('U'));
    }

    public function test_infer_interface_with_single_argument_works_properly(): void
    {
        try {
            [$resultA, $resultB] = $this->mapperBuilder
                ->infer(SomeInterface::class, function (string $type): string {
                    // @PHP8.0 use `match` with short closure
                    switch ($type) {
                        case 'classA-foo':
                            return SomeClassThatInheritsInterfaceA::class;
                        case 'classB-bar':
                            return SomeClassThatInheritsInterfaceB::class;
                        default:
                            self::fail("Type `$type` not handled.");
                    }
                })
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
            [$resultA, $resultB] = $this->mapperBuilder
                ->infer(SomeInterface::class, function (string $type, int $key): string {
                    if ($type === 'classA' && $key === 42) {
                        return SomeClassThatInheritsInterfaceA::class;
                    } elseif ($type === 'classB' && $key === 1337) {
                        return SomeClassThatInheritsInterfaceB::class;
                    }

                    self::fail("Combinaison `$type` / `$key` not handled.");
                })
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

    public function test_unresolvable_interface_implementation_throws_exception(): void
    {
        $this->expectException(CannotResolveTypeFromInterface::class);
        $this->expectExceptionCode(1618049116);
        $this->expectExceptionMessage('Impossible to resolve an implementation for the interface `' . SomeInterface::class . '`.');

        $this->mapperBuilder
            ->mapper()
            ->map(SomeInterface::class, []);
    }

    public function test_invalid_interface_resolver_return_type_throws_exception(): void
    {
        $this->expectException(InvalidInterfaceResolverReturnType::class);
        $this->expectExceptionCode(1630091260);
        $this->expectExceptionMessage('Invalid value 42; it must be the name of a class that implements `DateTimeInterface`.');

        $this->mapperBuilder
            ->infer(DateTimeInterface::class, fn () => 42)
            ->mapper()
            ->map(DateTimeInterface::class, []);
    }

    public function test_invalid_interface_resolved_return_type_throws_exception(): void
    {
        $this->expectException(InvalidTypeResolvedForInterface::class);
        $this->expectExceptionCode(1618049224);
        $this->expectExceptionMessage('Invalid type `int`; it must be the name of a class that implements `DateTimeInterface`.');

        $this->mapperBuilder
            ->infer(DateTimeInterface::class, fn () => 'int')
            ->mapper()
            ->map(DateTimeInterface::class, []);
    }

    public function test_invalid_resolved_interface_implementation_throws_exception(): void
    {
        $this->expectException(ResolvedTypeForInterfaceIsNotAccepted::class);
        $this->expectExceptionCode(1618049487);
        $this->expectExceptionMessage('The implementation `stdClass` is not accepted by the interface `DateTimeInterface`.');

        $this->mapperBuilder
            ->infer(DateTimeInterface::class, fn () => stdClass::class)
            ->mapper()
            ->map(DateTimeInterface::class, []);
    }

    public function test_invalid_source_throws_exception(): void
    {
        try {
            $this->mapperBuilder
                ->infer(SomeInterface::class, fn(string $type, int $key): string => SomeClassThatInheritsInterfaceA::class)
                ->mapper()
                ->map(SomeInterface::class, 42);
        } catch (MappingError $exception) {
            $error = $exception->node()->messages()[0];

            self::assertSame('1645283485', $error->code());
            self::assertSame('Invalid value 42: it must be an array.', (string)$error);
        }
    }

    public function test_invalid_source_value_throws_exception(): void
    {
        try {
            $this->mapperBuilder
                ->infer(SomeInterface::class, fn(int $key): string => SomeClassThatInheritsInterfaceA::class)
                ->mapper()
                ->map(SomeInterface::class, 'foo');
        } catch (MappingError $exception) {
            $error = $exception->node()->children()['key']->messages()[0];

            self::assertSame('1618736242', $error->code());
            self::assertSame("Cannot cast 'foo' to `int`.", (string)$error);
        }
    }

    public function test_exception_thrown_is_caught_and_throws_message_exception(): void
    {
        try {
            $this->mapperBuilder
                ->infer(DateTimeInterface::class, function () {
                    // @PHP8.0 use short closure
                    throw new DomainException('some error message', 1645303304);
                })
                ->mapper()
                ->map(DateTimeInterface::class, 'foo');
        } catch (MappingError $exception) {
            $error = $exception->node()->messages()[0];

            self::assertSame('1645303304', $error->code());
            self::assertSame('some error message', (string)$error);
        }
    }
}

interface SomeInterface
{
}

final class SomeClassThatInheritsInterfaceA implements SomeInterface
{
    public string $valueA;
}

final class SomeClassThatInheritsInterfaceB implements SomeInterface
{
    public string $valueB;
}
