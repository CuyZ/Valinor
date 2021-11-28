<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Definition;

use CuyZ\Valinor\Definition\MethodDefinition;
use CuyZ\Valinor\Definition\Parameters;
use CuyZ\Valinor\Tests\Fake\Definition\FakeParameterDefinition;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use PHPUnit\Framework\TestCase;

final class MethodDefinitionTest extends TestCase
{
    public function test_method_data_can_be_retrieved(): void
    {
        $name = 'someMethod';
        $signature = 'someMethodSignature';
        $parameters = new Parameters(FakeParameterDefinition::new());
        $isStatic = false;
        $isPublic = true;
        $returnType = new FakeType();

        $method = new MethodDefinition(
            $name,
            $signature,
            $parameters,
            $isStatic,
            $isPublic,
            $returnType
        );

        self::assertSame($name, $method->name());
        self::assertSame($signature, $method->signature());
        self::assertSame($parameters, $method->parameters());
        self::assertSame($isStatic, $method->isStatic());
        self::assertSame($isPublic, $method->isPublic());
        self::assertSame($returnType, $method->returnType());
    }
}
