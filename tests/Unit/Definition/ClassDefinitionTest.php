<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Definition;

use CuyZ\Valinor\Definition\ClassDefinition;
use CuyZ\Valinor\Definition\Methods;
use CuyZ\Valinor\Definition\Properties;
use CuyZ\Valinor\Tests\Fake\Definition\FakeAttributes;
use CuyZ\Valinor\Tests\Fake\Definition\FakeMethodDefinition;
use CuyZ\Valinor\Tests\Fake\Definition\FakePropertyDefinition;
use PHPUnit\Framework\TestCase;
use stdClass;

final class ClassDefinitionTest extends TestCase
{
    public function test_class_data_can_be_retrieved(): void
    {
        $name = stdClass::class;
        $signature = stdClass::class . '<string, stdClass>';
        $attributes = new FakeAttributes();
        $properties = new Properties(FakePropertyDefinition::new());
        $methods = new Methods(FakeMethodDefinition::new());

        $class = new ClassDefinition(
            $name,
            $signature,
            $attributes,
            $properties,
            $methods
        );

        self::assertSame($name, $class->name());
        self::assertSame($signature, $class->signature());
        self::assertSame($attributes, $class->attributes());
        self::assertSame($properties, $class->properties());
        self::assertSame($methods, $class->methods());
    }
}
