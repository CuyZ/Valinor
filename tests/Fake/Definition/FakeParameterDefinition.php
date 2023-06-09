<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fake\Definition;

use CuyZ\Valinor\Definition\ParameterDefinition;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Type\Type;
use ReflectionParameter;

final class FakeParameterDefinition
{
    private function __construct() {}

    public static function new(string $name = 'someParameter', Type $type = null): ParameterDefinition
    {
        return new ParameterDefinition(
            $name,
            $name,
            $type ?? new FakeType(),
            false,
            false,
            null,
            new FakeAttributes()
        );
    }

    public static function optional(string $name, Type $type, mixed $defaultValue): ParameterDefinition
    {
        return new ParameterDefinition(
            $name,
            $name,
            $type,
            true,
            false,
            $defaultValue,
            new FakeAttributes()
        );
    }

    public static function fromReflection(ReflectionParameter $reflection): ParameterDefinition
    {
        $type = new FakeType();

        if ($reflection->hasType()) {
            $type = FakeType::from($reflection->getType()->getName()); // @phpstan-ignore-line
        }

        return new ParameterDefinition(
            $reflection->name,
            'Signature::' . $reflection->name,
            $type,
            $reflection->isOptional(),
            $reflection->isVariadic(),
            $reflection->isDefaultValueAvailable() ? $reflection->getDefaultValue() : null,
            new FakeAttributes()
        );
    }
}
