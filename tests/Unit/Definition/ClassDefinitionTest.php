<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Definition;

use CuyZ\Valinor\Definition\Attributes;
use CuyZ\Valinor\Definition\ClassDefinition;
use CuyZ\Valinor\Definition\Methods;
use CuyZ\Valinor\Definition\Properties;
use CuyZ\Valinor\Tests\Fake\Definition\FakeMethodDefinition;
use CuyZ\Valinor\Tests\Fake\Definition\FakePropertyDefinition;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Tests\Unit\UnitTestCase;
use CuyZ\Valinor\Type\Types\NativeClassType;
use stdClass;

final class ClassDefinitionTest extends UnitTestCase
{
    public function test_class_data_can_be_retrieved(): void
    {
        $type = new NativeClassType(stdClass::class, [new FakeType()]);
        $attributes = new Attributes();
        $properties = new Properties(FakePropertyDefinition::new());
        $methods = new Methods(FakeMethodDefinition::new());

        $class = new ClassDefinition(stdClass::class, $type, $attributes, $properties, $methods, true, false);

        self::assertSame(stdClass::class, $class->name);
        self::assertSame($type, $class->type);
        self::assertSame($attributes, $class->attributes);
        self::assertSame($properties, $class->properties);
        self::assertSame($methods, $class->methods);
        self::assertSame(true, $class->isFinal);
        self::assertSame(false, $class->isAbstract);
    }
}
