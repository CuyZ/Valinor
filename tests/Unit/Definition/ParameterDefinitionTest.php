<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Definition;

use CuyZ\Valinor\Definition\Attributes;
use CuyZ\Valinor\Definition\ParameterDefinition;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use PHPUnit\Framework\TestCase;

final class ParameterDefinitionTest extends TestCase
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

        $parameter = new ParameterDefinition(
            $name,
            $signature,
            $type,
            $isOptional,
            $isVariadic,
            $defaultValue,
            $attributes
        );

        self::assertSame($name, $parameter->name);
        self::assertSame($signature, $parameter->signature);
        self::assertSame($type, $parameter->type);
        self::assertSame($isOptional, $parameter->isOptional);
        self::assertSame($isVariadic, $parameter->isVariadic);
        self::assertSame($defaultValue, $parameter->defaultValue);
        self::assertSame($attributes, $parameter->attributes);
    }
}
