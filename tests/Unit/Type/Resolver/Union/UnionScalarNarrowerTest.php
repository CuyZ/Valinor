<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Resolver\Union;

use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Type\IntegerType;
use CuyZ\Valinor\Type\Resolver\Exception\CannotResolveTypeFromUnion;
use CuyZ\Valinor\Type\Resolver\Union\UnionScalarNarrower;
use CuyZ\Valinor\Type\StringType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\BooleanType;
use CuyZ\Valinor\Type\Types\FloatType;
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
            FloatType::get(),
            NativeStringType::get(),
            BooleanType::get(),
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
                'Expected type' => FloatType::class,
            ],
            'int|float with stringed-float value' => [
                'Union type' => new UnionType(NativeIntegerType::get(), FloatType::get()),
                'Source' => '1337.42',
                'Expected type' => FloatType::class,
            ],
            'int|float|string|bool with string value' => [
                'Union type' => $scalarUnion,
                'Source' => 'foo',
                'Expected type' => StringType::class,
            ],
            'int|float|string|bool with boolean value' => [
                'Union type' => $scalarUnion,
                'Source' => true,
                'Expected type' => BooleanType::class,
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
        $unionType = new UnionType(FloatType::get(), NativeIntegerType::get());

        $type = $this->unionScalarNarrower->narrow($unionType, 42);

        self::assertInstanceOf(IntegerType::class, $type);
    }

    public function test_several_possible_types_throws_exception(): void
    {
        $unionType = new UnionType(BooleanType::get(), NativeIntegerType::get(), FloatType::get());

        $this->expectException(CannotResolveTypeFromUnion::class);
        $this->expectExceptionCode(1607027306);
        $this->expectExceptionMessage("Impossible to resolve the type from the union `$unionType` with a value of type `string`.");

        $this->unionScalarNarrower->narrow($unionType, '42');
    }

    public function test_null_value_but_null_not_in_union_throws_exception(): void
    {
        $unionType = new UnionType(new FakeType(), new FakeType());

        $this->expectException(CannotResolveTypeFromUnion::class);
        $this->expectExceptionCode(1607027306);
        $this->expectExceptionMessage("Impossible to resolve the type from the union `$unionType` with a value of type `null`.");

        $this->unionScalarNarrower->narrow($unionType, null);
    }
}
