<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping;

use CuyZ\Valinor\Mapper\Tree\Exception\CannotInferFinalClass;
use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Tests\Integration\IntegrationTest;
use LogicException;

final class ClassInheritanceInferringMappingTest extends IntegrationTest
{
    public function test_infer_abstract_class_works_as_expected(): void
    {
        $result = (new MapperBuilder())
            ->infer(
                SomeAbstractClass::class,
                fn () => SomeAbstractChildClass::class
            )
            ->mapper()
            ->map(SomeAbstractClass::class, [
                'foo' => 'foo',
                'bar' => 'bar',
                'baz' => 'baz',
            ]);

        self::assertInstanceOf(SomeAbstractChildClass::class, $result);
        self::assertSame('foo', $result->foo);
        self::assertSame('bar', $result->bar);
        self::assertSame('baz', $result->baz);
    }

    public function test_infer_abstract_class_with_argument_in_callback_works_as_expected(): void
    {
        $result = (new MapperBuilder())
            ->infer(
                SomeAbstractClass::class,
                /** @return class-string<SomeAbstractChildClass> */
                fn (string $type) => match ($type) {
                    'foo' => SomeAbstractChildClass::class,
                    default => throw new LogicException("Invalid type $type.")
                }
            )
            ->mapper()
            ->map(SomeAbstractClass::class, [
                'type' => 'foo',
                'foo' => 'foo',
                'bar' => 'bar',
                'baz' => 'baz',
            ]);

        self::assertInstanceOf(SomeAbstractChildClass::class, $result);
        self::assertSame('foo', $result->foo);
        self::assertSame('bar', $result->bar);
        self::assertSame('baz', $result->baz);
    }

    public function test_infer_class_works_as_expected(): void
    {
        $result = (new MapperBuilder())
            ->infer(
                SomeParentClass::class,
                fn () => SomeChildClass::class
            )
            ->mapper()
            ->map(SomeParentClass::class, [
                'foo' => 'foo',
                'bar' => 'bar',
                'baz' => 'baz',
            ]);

        self::assertInstanceOf(SomeChildClass::class, $result);
        self::assertSame('foo', $result->foo);
        self::assertSame('bar', $result->bar);
        self::assertSame('baz', $result->baz);
    }

    public function test_infer_class_with_argument_in_callback_works_as_expected(): void
    {
        $result = (new MapperBuilder())
            ->infer(
                SomeParentClass::class,
                /** @return class-string<SomeChildClass> */
                fn (string $type) => match ($type) {
                    'foo' => SomeChildClass::class,
                    default => throw new LogicException("Invalid type $type.")
                }
            )
            ->mapper()
            ->map(SomeParentClass::class, [
                'type' => 'foo',
                'foo' => 'foo',
                'bar' => 'bar',
                'baz' => 'baz',
            ]);

        self::assertInstanceOf(SomeChildClass::class, $result);
        self::assertSame('foo', $result->foo);
        self::assertSame('bar', $result->bar);
        self::assertSame('baz', $result->baz);
    }

    public function test_infer_final_class_throws_exception(): void
    {
        $this->expectException(CannotInferFinalClass::class);
        $this->expectExceptionCode(1671468163);
        $this->expectExceptionMessage('Cannot infer final class `' . SomeAbstractChildClass::class . '` with function');

        (new MapperBuilder())
            ->infer(SomeAbstractChildClass::class, fn () => SomeAbstractChildClass::class)
            ->mapper()
            ->map(SomeAbstractChildClass::class, []);
    }
}

abstract class SomeAbstractClass
{
    public string $foo;

    public string $bar;
}

final class SomeAbstractChildClass extends SomeAbstractClass
{
    public string $baz;
}

class SomeParentClass
{
    public string $foo;

    public string $bar;
}

final class SomeChildClass extends SomeParentClass
{
    public string $baz;
}
