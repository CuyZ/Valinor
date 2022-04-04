<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Resolver\Union;

use CuyZ\Valinor\Tests\Fake\Type\FakeObjectType;
use CuyZ\Valinor\Type\IntegerType;
use CuyZ\Valinor\Type\Resolver\Exception\CannotResolveTypeFromUnion;
use CuyZ\Valinor\Type\Resolver\Union\UnionScalarNarrower;
use CuyZ\Valinor\Type\StringType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\NativeBooleanType;
use CuyZ\Valinor\Type\Types\NativeFloatType;
use CuyZ\Valinor\Type\Types\NativeIntegerType;
use CuyZ\Valinor\Type\Types\NativeStringType;
use CuyZ\Valinor\Type\Types\UndefinedObjectType;
use CuyZ\Valinor\Type\Types\UnionType;
use PHPUnit\Framework\TestCase;
use stdClass;

final class UnionScalarNarrowerTest extends TestCase
{
    private UnionScalarNarrower $unionScalarNarrower;

    protected function setUp(): void
    {
        parent::setUp();

        $this->unionScalarNarrower = new UnionScalarNarrower();
    }

    /**
     * @dataProvider matching_types_are_resolved_data_provider
     *
     * @param mixed $source
     * @param class-string<Type> $expectedType
     */
    public function test_matching_types_are_resolved(UnionType $unionType, $source, string $expectedType): void
    {
        $type = $this->unionScalarNarrower->narrow($unionType, $source);

        self::assertInstanceOf($expectedType, $type);
    }

    public function matching_types_are_resolved_data_provider(): iterable
    {
        $scalarUnion = new UnionType(
            NativeIntegerType::get(),
            NativeFloatType::get(),
            NativeStringType::get(),
            NativeBooleanType::get(),
        );

        return [
            'int|float|string|bool with integer value' => [
                'Union type' => $scalarUnion,
                'Source' => 42,
                'Expected type' => IntegerType::class,
            ],
            'int|float|string|bool with float value' => [
                'Union type' => $scalarUnion,
                'Source' => 1337.42,
                'Expected type' => NativeFloatType::class,
            ],
            'int|float with stringed-float value' => [
                'Union type' => new UnionType(NativeIntegerType::get(), NativeFloatType::get()),
                'Source' => '1337.42',
                'Expected type' => NativeFloatType::class,
            ],
            'int|float|string|bool with string value' => [
                'Union type' => $scalarUnion,
                'Source' => 'foo',
                'Expected type' => StringType::class,
            ],
            'int|float|string|bool with boolean value' => [
                'Union type' => $scalarUnion,
                'Source' => true,
                'Expected type' => NativeBooleanType::class,
            ],
            'int|object with object value' => [
                'Union type' => new UnionType(NativeIntegerType::get(), UndefinedObjectType::get()),
                'Source' => new stdClass(),
                'Expected type' => UndefinedObjectType::class,
            ],
        ];
    }

    public function test_integer_type_is_narrowed_over_float_when_an_integer_value_is_given(): void
    {
        $unionType = new UnionType(NativeFloatType::get(), NativeIntegerType::get());

        $type = $this->unionScalarNarrower->narrow($unionType, 42);

        self::assertInstanceOf(IntegerType::class, $type);
    }

    public function test_several_possible_types_throws_exception(): void
    {
        $unionType = new UnionType(NativeBooleanType::get(), NativeIntegerType::get(), NativeFloatType::get());

        $this->expectException(CannotResolveTypeFromUnion::class);
        $this->expectExceptionCode(1607027306);
        $this->expectExceptionMessage("Value 'foo' does not match any of `bool`, `int`, `float`.");

        $this->unionScalarNarrower->narrow($unionType, 'foo');
    }

    public function test_several_possible_types_with_object_throws_exception(): void
    {
        $unionType = new UnionType(new FakeObjectType(), NativeIntegerType::get(), NativeFloatType::get());

        $this->expectException(CannotResolveTypeFromUnion::class);
        $this->expectExceptionCode(1607027306);
        $this->expectExceptionMessage("Value 'foo' is not accepted.");

        $this->unionScalarNarrower->narrow($unionType, 'foo');
    }

    public function test_null_value_but_null_not_in_union_throws_exception(): void
    {
        $unionType = new UnionType(NativeBooleanType::get(), NativeIntegerType::get(), NativeFloatType::get());

        $this->expectException(CannotResolveTypeFromUnion::class);
        $this->expectExceptionCode(1607027306);
        $this->expectExceptionMessage("Value null does not match any of `bool`, `int`, `float`.");

        $this->unionScalarNarrower->narrow($unionType, null);
    }
}
