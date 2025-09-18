<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Functional\Type\Dumper;

use CuyZ\Valinor\Mapper\Object\Constructor;
use CuyZ\Valinor\Tests\Functional\FunctionalTestCase;
use CuyZ\Valinor\Type\Dumper\TypeDumper;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\EnumType;
use CuyZ\Valinor\Type\Types\NativeClassType;
use CuyZ\Valinor\Type\Types\NativeIntegerType;
use CuyZ\Valinor\Type\Types\NativeStringType;
use PHPUnit\Framework\Attributes\DataProvider;

final class TypeDumperTest extends FunctionalTestCase
{
    #[DataProvider('type_dump_is_correct_data_provider')]
    public function test_type_dump_is_correct(Type $type, string $expected): void
    {
        $result = $this->getService(TypeDumper::class)->dump($type);

        self::assertSame($expected, $result);
    }

    /**
     * @return iterable<array{Type, string}>
     */
    public static function type_dump_is_correct_data_provider(): iterable
    {
        yield [
            'type' => new NativeStringType(),
            'expected' => 'string'
        ];

        yield [
            'type' => new NativeIntegerType(),
            'expected' => 'int'
        ];

        yield [
            'type' => new NativeClassType(WithTwoProperties::class),
            'expected' => 'array{foo: string, bar: int}'
        ];

        yield [
            'type' => new NativeClassType(WithTwoConstructors::class),
            'expected' => 'int|array{intValue: int, stringValue?: string}|array{intValue: int, twoProperties: array{foo: string, bar: int}}'
        ];

        yield [
            'type' => new NativeClassType(ObjectWithLotsOfProperties::class),
            'expected' => 'array{propertyA: string, propertyB: string, propertyC: string, propertyD: string, propertyE: string, propertyF: string, propertyG: string, propertyH: string, withTwoProperties: array{…}}'
        ];

        yield [
            'type' => EnumType::native(SomeEnum::class),
            'expected' => 'FOO|BAR|BAZ'
        ];

    }

}

class WithTwoProperties
{
    public string $foo;

    public int $bar;
}

class WithTwoConstructors
{
    #[Constructor]
    public static function fromScalarAndObject(int $intValue, WithTwoProperties $twoProperties): self
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

class ObjectWithLotsOfProperties
{
    public string $propertyA;
    public string $propertyB;
    public string $propertyC;
    public string $propertyD;
    public string $propertyE;
    public string $propertyF;
    public string $propertyG;
    public string $propertyH;
    public WithTwoProperties $withTwoProperties;
}

enum SomeEnum
{
    case FOO;
    case BAR;
    case BAZ;
}
