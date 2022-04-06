<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fake\Definition;

use CuyZ\Valinor\Definition\EmptyAttributes;
use CuyZ\Valinor\Definition\FunctionDefinition;
use CuyZ\Valinor\Definition\ParameterDefinition;
use CuyZ\Valinor\Definition\Parameters;
use CuyZ\Valinor\Type\Types\NativeStringType;
use stdClass;

final class FakeFunctionDefinition
{
    public static function new(): FunctionDefinition
    {
        return new FunctionDefinition(
            'foo',
            'foo:42-1337',
            'foo/bar',
            stdClass::class,
            new Parameters(
                new ParameterDefinition(
                    'bar',
                    'foo::bar',
                    NativeStringType::get(),
                    false,
                    false,
                    'foo',
                    EmptyAttributes::get()
                )
            ),
            NativeStringType::get()
        );
    }
}
