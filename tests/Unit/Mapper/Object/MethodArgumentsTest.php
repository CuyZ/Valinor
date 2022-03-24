<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Mapper\Object;

use CuyZ\Valinor\Definition\Parameters;
use CuyZ\Valinor\Mapper\Object\Exception\MissingMethodArgument;
use CuyZ\Valinor\Mapper\Object\MethodArguments;
use CuyZ\Valinor\Tests\Fake\Definition\FakeParameterDefinition;
use PHPUnit\Framework\TestCase;

final class MethodArgumentsTest extends TestCase
{
    public function test_missing_arguments_throws_exception(): void
    {
        $parameter = FakeParameterDefinition::new('foo');

        $this->expectException(MissingMethodArgument::class);
        $this->expectExceptionCode(1629468609);
        $this->expectExceptionMessage("Missing argument `foo` of type `{$parameter->type()}`.");

        (new MethodArguments(
            new Parameters($parameter),
            [
                'bar' => 'bar',
            ]
        ));
    }
}
