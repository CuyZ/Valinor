<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Definition;

use CuyZ\Valinor\Definition\Attributes;
use CuyZ\Valinor\Definition\MethodDefinition;
use CuyZ\Valinor\Definition\Parameters;
use CuyZ\Valinor\Tests\Fake\Definition\FakeAttributeDefinition;
use CuyZ\Valinor\Tests\Fake\Definition\FakeParameterDefinition;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Tests\Unit\UnitTestCase;

final class MethodDefinitionTest extends UnitTestCase
{
    public function test_method_data_can_be_retrieved(): void
    {
        $name = 'someMethod';
        $signature = 'someMethodSignature';
        $attributes = new Attributes(FakeAttributeDefinition::new());
        $parameters = new Parameters(FakeParameterDefinition::new());
        $isStatic = false;
        $isPublic = true;
        $returnType = new FakeType();

        $method = new MethodDefinition(
            $name,
            $signature,
            $attributes,
            $parameters,
            $isStatic,
            $isPublic,
            $returnType
        );

        self::assertSame($name, $method->name);
        self::assertSame($signature, $method->signature);
        self::assertSame($attributes, $method->attributes);
        self::assertSame($parameters, $method->parameters);
        self::assertSame($isStatic, $method->isStatic);
        self::assertSame($isPublic, $method->isPublic);
        self::assertSame($returnType, $method->returnType);
    }
}
