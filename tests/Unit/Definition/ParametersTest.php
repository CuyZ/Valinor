<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Definition;

use CuyZ\Valinor\Definition\Attributes;
use CuyZ\Valinor\Definition\ParameterDefinition;
use CuyZ\Valinor\Definition\Parameters;
use CuyZ\Valinor\Tests\Fake\Definition\FakeParameterDefinition;
use CuyZ\Valinor\Tests\Unit\UnitTestCase;
use CuyZ\Valinor\Type\Types\Generics;
use CuyZ\Valinor\Type\Types\GenericType;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\NativeStringType;

use function array_values;
use function iterator_to_array;

final class ParametersTest extends UnitTestCase
{
    public function test_parameter_can_be_found(): void
    {
        $parameter = FakeParameterDefinition::new();
        $parameters = new Parameters($parameter);

        self::assertFalse($parameters->has('unknownParameter'));

        self::assertTrue($parameters->has($parameter->name));
        self::assertSame($parameter, $parameters->get($parameter->name));
    }

    public function test_get_parameter_at_index_returns_correct_parameter(): void
    {
        $parameterA = FakeParameterDefinition::new('SomeParameterA');
        $parameterB = FakeParameterDefinition::new('SomeParameterB');
        $parameters = new Parameters($parameterA, $parameterB);

        self::assertSame($parameterA, $parameters->at(0));
        self::assertSame($parameterB, $parameters->at(1));
    }

    public function test_parameters_to_list_returns_list(): void
    {
        $parameterA = FakeParameterDefinition::new('SomeParameterA');
        $parameterB = FakeParameterDefinition::new('SomeParameterB');
        $parameters = new Parameters($parameterA, $parameterB);

        self::assertSame(['SomeParameterA' => $parameterA, 'SomeParameterB' => $parameterB], $parameters->toArray());
    }

    public function test_parameters_can_have_generics_assigned(): void
    {
        $parameters = new Parameters(
            new ParameterDefinition(
                name: 'SomeParameterA',
                signature: 'someSignature',
                type: new GenericType('T', new MixedType()),
                nativeType: new MixedType(),
                isOptional: true,
                isVariadic: false,
                defaultValue: null,
                attributes: Attributes::empty(),
            ),
        );

        $parameters = $parameters->assignGenerics(new Generics(['T' => new NativeStringType()]));

        self::assertInstanceOf(NativeStringType::class, $parameters->at(0)->type);
    }

    public function test_parameters_are_countable(): void
    {
        $parameters = new Parameters(
            FakeParameterDefinition::new('parameterA'),
            FakeParameterDefinition::new('parameterB'),
            FakeParameterDefinition::new('parameterC'),
        );

        self::assertCount(3, $parameters);
    }

    public function test_parameters_are_iterable(): void
    {
        $parametersInstances = [
            'parameterA' => FakeParameterDefinition::new('parameterA'),
            'parameterB' => FakeParameterDefinition::new('parameterB'),
            'parameterC' => FakeParameterDefinition::new('parameterC'),
        ];

        $parameters = new Parameters(...array_values($parametersInstances));

        self::assertSame($parametersInstances, iterator_to_array($parameters));
    }
}
