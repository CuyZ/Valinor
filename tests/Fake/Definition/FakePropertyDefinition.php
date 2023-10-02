<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fake\Definition;

use CuyZ\Valinor\Definition\PropertyDefinition;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Type\Type;
use ReflectionProperty;

final class FakePropertyDefinition
{
    private function __construct() {}

    public static function new(string $name = 'someProperty'): PropertyDefinition
    {
        return new PropertyDefinition(
            $name,
            $name,
            new FakeType(),
            false,
            null,
            false,
            new FakeAttributes()
        );
    }

    public static function fromReflection(ReflectionProperty $reflection): PropertyDefinition
    {
        $defaultProperties = $reflection->getDeclaringClass()->getDefaultProperties();
        $type = new FakeType();

        if ($reflection->hasType()) {
            $type = FakeType::from($reflection->getType()->getName()); // @phpstan-ignore-line
        }

        return new PropertyDefinition(
            $reflection->name,
            'Signature::' . $reflection->name,
            $type,
            isset($defaultProperties[$reflection->name]),
            $defaultProperties[$reflection->name] ?? null,
            $reflection->isPublic(),
            new FakeAttributes()
        );
    }

    public static function withType(Type $type): PropertyDefinition
    {
        return new PropertyDefinition(
            'someProperty',
            'someProperty',
            $type,
            false,
            null,
            false,
            new FakeAttributes()
        );
    }
}
