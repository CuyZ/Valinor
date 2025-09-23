<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Functional\Type\Dumper;

use CuyZ\Valinor\Library\Settings;
use CuyZ\Valinor\Mapper\Object\Constructor;
use CuyZ\Valinor\Tests\Functional\FunctionalTestCase;
use CuyZ\Valinor\Type\Dumper\TypeDumper;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\ArrayKeyType;
use CuyZ\Valinor\Type\Types\ArrayType;
use CuyZ\Valinor\Type\Types\EnumType;
use CuyZ\Valinor\Type\Types\IntegerValueType;
use CuyZ\Valinor\Type\Types\InterfaceType;
use CuyZ\Valinor\Type\Types\IterableType;
use CuyZ\Valinor\Type\Types\ListType;
use CuyZ\Valinor\Type\Types\NativeClassType;
use CuyZ\Valinor\Type\Types\NativeIntegerType;
use CuyZ\Valinor\Type\Types\NativeStringType;
use CuyZ\Valinor\Type\Types\NonEmptyArrayType;
use CuyZ\Valinor\Type\Types\NonEmptyListType;
use CuyZ\Valinor\Type\Types\ShapedArrayElement;
use CuyZ\Valinor\Type\Types\ShapedArrayType;
use CuyZ\Valinor\Type\Types\StringValueType;
use CuyZ\Valinor\Type\Types\UnionType;
use CuyZ\Valinor\Type\Types\UnresolvableType;
use PHPUnit\Framework\Attributes\DataProvider;

interface SomeInterface {}

final class TypeDumperTest extends FunctionalTestCase
{
    #[DataProvider('type_dump_is_correct_data_provider')]
    public function test_type_dump_is_correct(Type $type, string $expected, Settings $settings = new Settings()): void
    {
        $result = $this->getService(TypeDumper::class, $settings)->dump($type);

        self::assertSame($expected, $result);
    }

    public static function type_dump_is_correct_data_provider(): iterable
    {
        yield 'unresolvable' => [
            'type' => new UnresolvableType('Unknown type', 'some message'),
            'expected' => '*unknown*',
        ];

        yield 'string' => [
            'type' => new NativeStringType(),
            'expected' => 'string',
        ];

        yield 'integer' => [
            'type' => new NativeIntegerType(),
            'expected' => 'int',
        ];

        yield 'enum' => [
            'type' => EnumType::native(SomeEnum::class),
            'expected' => 'FOO|BAR|BAZ',
        ];

        yield 'class with two properties' => [
            'type' => new NativeClassType(ClassWithTwoProperties::class),
            'expected' => 'array{foo: string, bar: int}',
        ];

        yield 'class with several constructors' => [
            'type' => new NativeClassType(ClassWithSeveralConstructors::class),
            'expected' => 'int|array{intValue: int, stringValue?: string}|array{intValue: int, twoProperties: array{foo: string, bar: int}}',
        ];

        yield 'class with lots of properties' => [
            'type' => new NativeClassType(ClassWithLotsOfProperties::class),
            'expected' => 'array{firstObject: array{foo: string, bar: int}, propertyA: string, propertyB: string, propertyC: string, propertyD: string, propertyE: string, propertyF: string, secondObject: int|array{…}}',
        ];

        yield 'array of class (with no array-key)' => [
            'type' => new ArrayType(ArrayKeyType::default(), new NativeClassType(ClassWithTwoProperties::class)),
            'expected' => 'array<array{foo: string, bar: int}>',
        ];

        yield 'array of class (with integer key)' => [
            'type' => new ArrayType(ArrayKeyType::integer(), new NativeClassType(ClassWithTwoProperties::class)),
            'expected' => 'array<int, array{foo: string, bar: int}>',
        ];

        yield 'array of class (with complex key)' => [
            'type' => new ArrayType(new ArrayKeyType([new IntegerValueType(42), new StringValueType('foo')]), new NativeClassType(ClassWithTwoProperties::class)),
            'expected' => 'array<42|foo, array{foo: string, bar: int}>',
        ];

        yield 'non-empty-array of class (with no array-key)' => [
            'type' => new NonEmptyArrayType(ArrayKeyType::default(), new NativeClassType(ClassWithTwoProperties::class)),
            'expected' => 'non-empty-array<array{foo: string, bar: int}>',
        ];

        yield 'non-empty-array of class (with integer key)' => [
            'type' => new NonEmptyArrayType(ArrayKeyType::integer(), new NativeClassType(ClassWithTwoProperties::class)),
            'expected' => 'non-empty-array<int, array{foo: string, bar: int}>',
        ];

        yield 'iterable of class (with no array-key)' => [
            'type' => new IterableType(ArrayKeyType::default(), new NativeClassType(ClassWithTwoProperties::class)),
            'expected' => 'iterable<array{foo: string, bar: int}>',
        ];

        yield 'iterable of class (with integer key)' => [
            'type' => new IterableType(ArrayKeyType::integer(), new NativeClassType(ClassWithTwoProperties::class)),
            'expected' => 'iterable<int, array{foo: string, bar: int}>',
        ];

        yield 'list of class' => [
            'type' => new ListType(new NativeClassType(ClassWithTwoProperties::class)),
            'expected' => 'list<array{foo: string, bar: int}>',
        ];

        yield 'non-empty-list of class' => [
            'type' => new NonEmptyListType(new NativeClassType(ClassWithTwoProperties::class)),
            'expected' => 'non-empty-list<array{foo: string, bar: int}>',
        ];

        yield 'shaped array' => [
            'type' => new ShapedArrayType([
                new ShapedArrayElement(
                    key: new StringValueType('classWithTwoProperties'),
                    type: new NativeClassType(ClassWithTwoProperties::class),
                ),
                new ShapedArrayElement(
                    key: new StringValueType('integer'),
                    type: new NativeIntegerType(),
                    optional: true,
                ),
            ]),
            'expected' => 'array{classWithTwoProperties: array{foo: string, bar: int}, integer?: int}',
        ];

        yield 'union of classes' => [
            'type' => new UnionType(new NativeClassType(ClassWithTwoProperties::class), new NativeClassType(WithThreeProperties::class)),
            'expected' => 'array{foo: string, bar: int}|array{foo: string, bar: int, baz: bool}',
        ];

        yield 'interface without custom constructor nor inferring constructor' => [
            'type' => new InterfaceType(SomeInterface::class),
            'expected' => '*unknown*',
        ];

        $settings = new Settings();
        $settings->customConstructors = [
            fn (string $foo, int $bar = 42): SomeInterface => new SomeClassImplementingInterface(),
        ];

        yield 'interface with custom constructor' => [
            'type' => new InterfaceType(SomeInterface::class),
            'expected' => 'array{foo: string, bar?: int}',
            'settings' => $settings,
        ];

        $settings = new Settings();
        $settings->inferredMapping = [
            SomeInterface::class =>
                /** @return class-string<SomeClassImplementingInterface> */
                fn (string $type, int $intValue) => SomeClassImplementingInterface::class,
        ];

        yield 'interface with inferring constructor' => [
            'type' => new InterfaceType(SomeInterface::class),
            'expected' => 'array{type: string, intValue: int}|array{type: string, intValue: int, stringValue?: string}',
            'settings' => $settings,
        ];

        $settings = new Settings();
        $settings->inferredMapping = [
            SomeInterface::class =>
                /** @return class-string<SomeClassImplementingInterface|AnotherClassImplementingInterface> */
                fn (string $type, int $size) => SomeClassImplementingInterface::class,
        ];

        yield 'interface with inferring constructor with classes that have common constructors' => [
            'type' => new InterfaceType(SomeInterface::class),
            'expected' => 'array{type: string, size: int, intValue: int}|array{type: string, size: int, stringValue: string}|array{type: string, size: int, stringValue: string, intValue: int}',
            'settings' => $settings,
        ];
    }
}

class ClassWithTwoProperties
{
    public string $foo;

    public int $bar;
}

class WithThreeProperties
{
    public string $foo;

    public int $bar;

    public bool $baz;
}

class ClassWithSeveralConstructors
{
    #[Constructor]
    public static function fromScalarAndObject(int $intValue, ClassWithTwoProperties $twoProperties): self
    {
        return new self();
    }

    #[Constructor]
    public static function fromTwoScalars(int $intValue, string $stringValue = 'foo'): self
    {
        return new self();
    }

    #[Constructor]
    public static function fromInt(int $intValue): self
    {
        return new self();
    }
}

class ClassWithLotsOfProperties
{
    public ClassWithTwoProperties $firstObject;
    public string $propertyA;
    public string $propertyB;
    public string $propertyC;
    public string $propertyD;
    public string $propertyE;
    public string $propertyF;
    public ClassWithSeveralConstructors $secondObject;
}

enum SomeEnum
{
    case FOO;
    case BAR;
    case BAZ;
}

final class SomeClassImplementingInterface implements SomeInterface
{
    #[Constructor]
    public static function fromTwoScalars(int $intValue, string $stringValue = 'foo'): self
    {
        return new self();
    }

    #[Constructor]
    public static function fromInt(int $intValue): self
    {
        return new self();
    }
}

final class AnotherClassImplementingInterface implements SomeInterface
{
    #[Constructor]
    public static function fromTwoScalars(string $stringValue, int $intValue): self
    {
        return new self();
    }

    #[Constructor]
    public static function fromString(string $stringValue): self
    {
        return new self();
    }
}
