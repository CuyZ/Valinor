<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Mapper\Object;

use CuyZ\Valinor\Definition\FunctionObject;
use CuyZ\Valinor\Mapper\Object\FunctionObjectBuilder;
use CuyZ\Valinor\Tests\Fake\Definition\FakeFunctionDefinition;
use PHPUnit\Framework\TestCase;
use stdClass;

final class FunctionObjectBuilderTest extends TestCase
{
    public function test_arguments_instance_stays_the_same(): void
    {
        $objectBuilder = new FunctionObjectBuilder(new FunctionObject(FakeFunctionDefinition::new(), fn () => new stdClass()));

        $argumentsA = $objectBuilder->describeArguments();
        $argumentsB = $objectBuilder->describeArguments();

        self::assertSame($argumentsA, $argumentsB);
    }
}
