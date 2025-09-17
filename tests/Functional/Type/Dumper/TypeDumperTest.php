<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Functional\Type\Dumper;

use CuyZ\Valinor\Library\Container;
use CuyZ\Valinor\Library\Settings;
use CuyZ\Valinor\Mapper\Object\Constructor;
use CuyZ\Valinor\Type\Dumper\TypeDumper;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\NativeClassType;
use CuyZ\Valinor\Type\Types\NativeIntegerType;
use CuyZ\Valinor\Type\Types\NativeStringType;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

final class TypeDumperTest extends TestCase
{
    #[TestWith([new NativeStringType(), 'string'])]
    #[TestWith([new NativeIntegerType(), 'int'])]
    #[TestWith([new NativeClassType(WithTwoProperties::class), 'array{foo: string, bar: int}'])]
    #[TestWith([new NativeClassType(WithTwoConstructors::class), 'array{intValue: int, stringValue: string}|array{intValue: int, twoProperties: array{foo: string, bar: int}}|int'])]
    public function test_type_dump_is_correct(Type $type, string $expected): void
    {
        $settings = new Settings();
        $container = new Container($settings);
        $typeDumper = $container->get(TypeDumper::class);
        $result = $typeDumper->dump($type);

        self::assertSame($expected, $result);
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
    public static function fromTwoScalars(int $intValue, string $stringValue): self
    {
        return new self();
    }

    #[Constructor]
    public static function fromInt(int $intValue): self
    {
        return new self();
    }
}
