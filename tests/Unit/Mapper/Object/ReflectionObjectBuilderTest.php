<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Mapper\Object;

use CuyZ\Valinor\Mapper\Object\ReflectionObjectBuilder;
use CuyZ\Valinor\Tests\Fake\Definition\FakeClassDefinition;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class ReflectionObjectBuilderTest extends TestCase
{
    public function test_build_object_without_constructor_returns_correct_object(): void
    {
        $object = new class () {
            public string $valueA;

            protected string $valueB;

            private string $valueC = 'Some property default value';

            public function valueA(): string
            {
                return $this->valueA;
            }

            public function valueB(): string
            {
                return $this->valueB;
            }

            public function valueC(): string
            {
                return $this->valueC;
            }
        };

        $class = FakeClassDefinition::fromReflection(new ReflectionClass($object));
        $objectBuilder = new ReflectionObjectBuilder($class);
        $result = $objectBuilder->build([
            'valueA' => 'valueA',
            'valueB' => 'valueB',
            'valueC' => 'valueC',
        ]);

        self::assertSame('valueA', $result->valueA()); // @phpstan-ignore-line
        self::assertSame('valueB', $result->valueB()); // @phpstan-ignore-line
        self::assertSame('valueC', $result->valueC()); // @phpstan-ignore-line
    }

    public function test_arguments_instance_stays_the_same(): void
    {
        $class = FakeClassDefinition::new();
        $objectBuilder = new ReflectionObjectBuilder($class);

        $argumentsA = $objectBuilder->describeArguments();
        $argumentsB = $objectBuilder->describeArguments();

        self::assertSame($argumentsA, $argumentsB);
    }
}
