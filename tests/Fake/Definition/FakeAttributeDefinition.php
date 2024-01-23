<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fake\Definition;

use CuyZ\Valinor\Definition\AttributeDefinition;
use ReflectionAttribute;
use ReflectionClass;
use stdClass;

final class FakeAttributeDefinition
{
    /**
     * @param class-string $name
     */
    public static function new(string $name = stdClass::class): AttributeDefinition
    {
        return new AttributeDefinition(
            FakeClassDefinition::new($name),
            [],
        );
    }

    /**
     * @param ReflectionAttribute<object> $reflection
     */
    public static function fromReflection(ReflectionAttribute $reflection): AttributeDefinition
    {
        $classReflection = new ReflectionClass($reflection->getName());

        return new AttributeDefinition(
            FakeClassDefinition::fromReflection($classReflection),
            $reflection->getArguments(),
        );
    }
}
