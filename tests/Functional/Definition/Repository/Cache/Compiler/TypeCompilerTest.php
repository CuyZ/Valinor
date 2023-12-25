<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Functional\Definition\Repository\Cache\Compiler;

use CuyZ\Valinor\Definition\Repository\Cache\Compiler\TypeCompiler;
use CuyZ\Valinor\Tests\Fixture\Enum\PureEnum;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\ArrayKeyType;
use CuyZ\Valinor\Type\Types\ArrayType;
use CuyZ\Valinor\Type\Types\BooleanValueType;
use CuyZ\Valinor\Type\Types\ClassStringType;
use CuyZ\Valinor\Type\Types\NativeClassType;
use CuyZ\Valinor\Type\Types\FloatValueType;
use CuyZ\Valinor\Type\Types\IntegerRangeType;
use CuyZ\Valinor\Type\Types\IntegerValueType;
use CuyZ\Valinor\Type\Types\InterfaceType;
use CuyZ\Valinor\Type\Types\IntersectionType;
use CuyZ\Valinor\Type\Types\IterableType;
use CuyZ\Valinor\Type\Types\ListType;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\NativeBooleanType;
use CuyZ\Valinor\Type\Types\EnumType;
use CuyZ\Valinor\Type\Types\NativeFloatType;
use CuyZ\Valinor\Type\Types\NativeIntegerType;
use CuyZ\Valinor\Type\Types\NativeStringType;
use CuyZ\Valinor\Type\Types\NegativeIntegerType;
use CuyZ\Valinor\Type\Types\NonEmptyArrayType;
use CuyZ\Valinor\Type\Types\NonEmptyListType;
use CuyZ\Valinor\Type\Types\NonEmptyStringType;
use CuyZ\Valinor\Type\Types\NonNegativeIntegerType;
use CuyZ\Valinor\Type\Types\NonPositiveIntegerType;
use CuyZ\Valinor\Type\Types\NullType;
use CuyZ\Valinor\Type\Types\NumericStringType;
use CuyZ\Valinor\Type\Types\PositiveIntegerType;
use CuyZ\Valinor\Type\Types\ShapedArrayElement;
use CuyZ\Valinor\Type\Types\ShapedArrayType;
use CuyZ\Valinor\Type\Types\StringValueType;
use CuyZ\Valinor\Type\Types\UndefinedObjectType;
use CuyZ\Valinor\Type\Types\UnionType;
use CuyZ\Valinor\Type\Types\UnresolvableType;
use DateTime;
use DateTimeInterface;
use Error;
use PHPUnit\Framework\TestCase;
use stdClass;

final class TypeCompilerTest extends TestCase
{
    private TypeCompiler $typeCompiler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->typeCompiler = new TypeCompiler();
    }

    /**
     * @dataProvider type_is_compiled_correctly_data_provider
     */
    public function test_type_is_compiled_correctly(Type $type): void
    {
        $code = $this->typeCompiler->compile($type);

        try {
            $compiledType = eval("return $code;");
        } catch (Error $exception) {
            self::fail($exception->getMessage());
        }

        self::assertInstanceOf($type::class, $compiledType);
        self::assertSame($type->toString(), $compiledType->toString());
    }

    public function type_is_compiled_correctly_data_provider(): iterable
    {
        yield [NullType::get()];
        yield [BooleanValueType::true()];
        yield [BooleanValueType::false()];
        yield [NativeBooleanType::get()];
        yield [NativeFloatType::get()];
        yield [new FloatValueType(1337.42)];
        yield [new FloatValueType(-1337.42)];
        yield [NativeIntegerType::get()];
        yield [PositiveIntegerType::get()];
        yield [NegativeIntegerType::get()];
        yield [NonPositiveIntegerType::get()];
        yield [NonNegativeIntegerType::get()];
        yield [new IntegerValueType(1337)];
        yield [new IntegerValueType(-1337)];
        yield [new IntegerRangeType(42, 1337)];
        yield [new IntegerRangeType(-1337, -42)];
        yield [new IntegerRangeType(PHP_INT_MIN, PHP_INT_MAX)];
        yield [NativeStringType::get()];
        yield [NonEmptyStringType::get()];
        yield [NumericStringType::get()];
        yield [UndefinedObjectType::get()];
        yield [MixedType::get()];
        yield [new InterfaceType(DateTimeInterface::class, ['Template' => NativeStringType::get()])];
        yield [new NativeClassType(stdClass::class, ['Template' => NativeStringType::get()])];
        yield [new IntersectionType(new InterfaceType(DateTimeInterface::class), new NativeClassType(DateTime::class))];

        if (PHP_VERSION_ID >= 8_01_00) {
            yield [EnumType::native(PureEnum::class)];
            yield [EnumType::fromPattern(PureEnum::class, 'BA*')];
        }

        yield [new UnionType(NativeStringType::get(), NativeIntegerType::get(), NativeFloatType::get())];
        yield [ArrayType::native()];
        yield [new ArrayType(ArrayKeyType::default(), NativeFloatType::get())];
        yield [new ArrayType(ArrayKeyType::integer(), NativeIntegerType::get())];
        yield [new ArrayType(ArrayKeyType::string(), NativeStringType::get())];
        yield [NonEmptyArrayType::native()];
        yield [new NonEmptyArrayType(ArrayKeyType::default(), NativeFloatType::get())];
        yield [new NonEmptyArrayType(ArrayKeyType::integer(), NativeIntegerType::get())];
        yield [new NonEmptyArrayType(ArrayKeyType::string(), NativeStringType::get())];
        yield [ListType::native()];
        yield [new ListType(NativeFloatType::get())];
        yield [new ListType(NativeIntegerType::get())];
        yield [new ListType(NativeStringType::get())];
        yield [NonEmptyListType::native()];
        yield [new NonEmptyListType(NativeFloatType::get())];
        yield [new NonEmptyListType(NativeIntegerType::get())];
        yield [new NonEmptyListType(NativeStringType::get())];
        yield [new ShapedArrayType(
            new ShapedArrayElement(new StringValueType('foo'), NativeStringType::get()),
            new ShapedArrayElement(new IntegerValueType(1337), NativeIntegerType::get(), true)
        )];
        yield [new IterableType(ArrayKeyType::default(), NativeFloatType::get())];
        yield [new IterableType(ArrayKeyType::integer(), NativeIntegerType::get())];
        yield [new IterableType(ArrayKeyType::string(), NativeStringType::get())];
        yield [new ClassStringType()];
        yield [new ClassStringType(new NativeClassType(stdClass::class))];
        yield [new ClassStringType(new InterfaceType(DateTimeInterface::class))];
        yield [new UnresolvableType('some-type', 'some message')];
    }

    public function test_class_parent_is_compiled_properly(): void
    {
        $type = new NativeClassType(
            stdClass::class,
            parent: new NativeClassType(
                stdClass::class,
                ['Template' => NativeStringType::get()],
            )
        );

        $code = $this->typeCompiler->compile($type);

        try {
            $compiledType = eval("return $code;");
        } catch (Error $exception) {
            self::fail($exception->getMessage());
        }

        self::assertInstanceOf(NativeClassType::class, $compiledType);
        self::assertInstanceOf(NativeStringType::class, $compiledType->parent()->generics()['Template']);
    }
}
