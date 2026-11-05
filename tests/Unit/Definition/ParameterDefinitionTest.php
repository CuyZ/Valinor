<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Definition;

use CuyZ\Valinor\Definition\Attributes;
use CuyZ\Valinor\Definition\ParameterDefinition;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Tests\Fixture\Enum\PureEnum;
use CuyZ\Valinor\Tests\Unit\UnitTestCase;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\EnumType;
use CuyZ\Valinor\Type\Types\Generics;
use CuyZ\Valinor\Type\Types\GenericType;
use CuyZ\Valinor\Type\Types\IntegerValueType;
use CuyZ\Valinor\Type\Types\ListType;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\NativeIntegerType;
use CuyZ\Valinor\Type\Types\NullType;
use PHPUnit\Framework\Attributes\DataProvider;

final class ParameterDefinitionTest extends UnitTestCase
{
    public function test_parameter_data_can_be_retrieved(): void
    {
        $name = 'someParameter';
        $signature = 'someParameterSignature';
        $type = new FakeType();
        $isOptional = true;
        $isVariadic = true;
        $defaultValue = 'Some parameter default value';
        $attributes = new Attributes();
        $isFixed = true;

        $parameter = new ParameterDefinition(
            $name,
            $signature,
            $type,
            $type,
            $isOptional,
            $isVariadic,
            $defaultValue,
            $attributes,
            $isFixed
        );

        self::assertSame($name, $parameter->name);
        self::assertSame($signature, $parameter->signature);
        self::assertSame($type, $parameter->type);
        self::assertSame($isOptional, $parameter->isOptional);
        self::assertSame($isVariadic, $parameter->isVariadic);
        self::assertSame($defaultValue, $parameter->defaultValue);
        self::assertSame($attributes, $parameter->attributes);
        self::assertSame($isFixed, $parameter->isFixed);
    }

    #[DataProvider('parameter_typed_with_template_is_fixed_when_template_is_assigned_to_single_value_data_provider')]
    public function test_parameter_typed_with_template_is_fixed_when_template_is_assigned_to_single_value(Type $generic, bool $expectedIsFixed): void
    {
        $parameter = new ParameterDefinition(
            name: 'someParameter',
            signature: 'someSignature',
            type: new GenericType('T', new MixedType()),
            nativeType: new MixedType(),
            isOptional: false,
            isVariadic: false,
            defaultValue: null,
            attributes: Attributes::empty(),
            isFixed: false,
        );

        $parameter = $parameter->assignGenerics(new Generics(['T' => $generic]));

        self::assertSame($expectedIsFixed, $parameter->isFixed);
    }

    public static function parameter_typed_with_template_is_fixed_when_template_is_assigned_to_single_value_data_provider(): iterable
    {
        yield 'integer value' => [new IntegerValueType(42), true];
        yield 'null' => [NullType::get(), true];
        yield 'enum with single case' => [EnumType::fromPattern(PureEnum::class, 'FOO'), true];
        yield 'enum with several cases' => [EnumType::native(PureEnum::class), false];
        yield 'native integer' => [NativeIntegerType::get(), false];
    }

    public function test_parameter_not_typed_with_template_keeps_fixed_state_when_generics_are_assigned(): void
    {
        $parameter = new ParameterDefinition(
            name: 'someParameter',
            signature: 'someSignature',
            type: new ListType(new GenericType('T', new MixedType())),
            nativeType: new MixedType(),
            isOptional: false,
            isVariadic: false,
            defaultValue: null,
            attributes: Attributes::empty(),
            isFixed: false,
        );

        $parameter = $parameter->assignGenerics(new Generics(['T' => new IntegerValueType(42)]));

        self::assertFalse($parameter->isFixed);
    }
}
