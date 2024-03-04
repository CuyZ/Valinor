<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fake\Definition;

use CuyZ\Valinor\Definition\Attributes;
use CuyZ\Valinor\Definition\ParameterDefinition;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Type\Type;
use ReflectionParameter;

final class FakeParameterDefinition
{
    private function __construct() {}

    /**
     * @param non-empty-string $name
     */
    public static function new(string $name = 'someParameter', Type $type = null): ParameterDefinition
    {
        return new ParameterDefinition(
            $name,
            $name,
            $type ?? new FakeType(),
            $type ?? new FakeType(),
            false,
            false,
            null,
            new Attributes()
        );
    }

    /**
     * @param non-empty-string $name
     */
    public static function optional(string $name, Type $type, mixed $defaultValue): ParameterDefinition
    {
        return new ParameterDefinition(
            $name,
            $name,
            $type,
            $type,
            true,
            false,
            $defaultValue,
            new Attributes()
        );
    }

    public static function fromReflection(ReflectionParameter $reflection): ParameterDefinition
    {
        /** @var non-empty-string $name */
        $name = $reflection->name;
        $type = new FakeType();

        if ($reflection->hasType()) {
            $type = FakeType::from($reflection->getType()->getName()); // @phpstan-ignore-line
        }

        return new ParameterDefinition(
            $name,
            'Signature::' . $reflection->name,
            $type,
            $type,
            $reflection->isOptional(),
            $reflection->isVariadic(),
            $reflection->isDefaultValueAvailable() ? $reflection->getDefaultValue() : null,
            new Attributes()
        );
    }
}
