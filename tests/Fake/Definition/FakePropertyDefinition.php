<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fake\Definition;

use CuyZ\Valinor\Definition\Attributes;
use CuyZ\Valinor\Definition\PropertyDefinition;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Type\Types\MixedType;
use ReflectionProperty;

final class FakePropertyDefinition
{
    private function __construct() {}

    /**
     * @param non-empty-string $name
     */
    public static function new(string $name = 'someProperty'): PropertyDefinition
    {
        return new PropertyDefinition(
            $name,
            $name,
            new MixedType(),
            false,
            null,
            false,
            new Attributes()
        );
    }

    public static function fromReflection(ReflectionProperty $reflection): PropertyDefinition
    {
        /** @var non-empty-string $name */
        $name = $reflection->name;
        $defaultProperties = $reflection->getDeclaringClass()->getDefaultProperties();
        $type = new MixedType();

        if ($reflection->hasType()) {
            $type = FakeType::from($reflection->getType()->getName()); // @phpstan-ignore-line
        }

        return new PropertyDefinition(
            $name,
            'Signature::' . $reflection->name,
            $type,
            isset($defaultProperties[$reflection->name]),
            $defaultProperties[$reflection->name] ?? null,
            $reflection->isPublic(),
            new Attributes()
        );
    }
}
