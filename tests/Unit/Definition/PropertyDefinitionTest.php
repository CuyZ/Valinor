<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Definition;

use CuyZ\Valinor\Definition\PropertyDefinition;
use CuyZ\Valinor\Tests\Fake\Definition\FakeAttributes;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use PHPUnit\Framework\TestCase;

final class PropertyDefinitionTest extends TestCase
{
    public function test_property_data_can_be_retrieved(): void
    {
        $name = 'someProperty';
        $signature = 'somePropertySignature';
        $type = new FakeType();
        $hasDefaultValue = true;
        $defaultValue = 'Some property default value';
        $isPublic = true;
        $attributes = new FakeAttributes();

        $property = new PropertyDefinition(
            $name,
            $signature,
            $type,
            $hasDefaultValue,
            $defaultValue,
            $isPublic,
            $attributes
        );

        self::assertSame($name, $property->name());
        self::assertSame($signature, $property->signature());
        self::assertSame($type, $property->type());
        self::assertSame($hasDefaultValue, $property->hasDefaultValue());
        self::assertSame($defaultValue, $property->defaultValue());
        self::assertSame($isPublic, $property->isPublic());
        self::assertSame($attributes, $property->attributes());
    }
}
