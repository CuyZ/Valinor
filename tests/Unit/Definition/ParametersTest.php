<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Definition;

use CuyZ\Valinor\Definition\Parameters;
use CuyZ\Valinor\Tests\Fake\Definition\FakeParameterDefinition;
use CuyZ\Valinor\Tests\Traits\IteratorTester;
use PHPUnit\Framework\TestCase;

use function array_values;

final class ParametersTest extends TestCase
{
    use IteratorTester;

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

        $this->checkIterable($parameters, $parametersInstances);
    }
}
