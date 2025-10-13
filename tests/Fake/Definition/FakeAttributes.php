<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fake\Definition;

use CuyZ\Valinor\Definition\Attributes;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;
use Reflector;
use ReflectionAttribute;

final class FakeAttributes
{
    /**
     * @param ReflectionClass<covariant object>|ReflectionProperty|ReflectionMethod|ReflectionFunction|ReflectionParameter $reflection
     */
    public static function fromReflection(Reflector $reflection): Attributes
    {
        return new Attributes(
            ...array_map(
                static fn (ReflectionAttribute $reflection) => FakeAttributeDefinition::fromReflection($reflection),
                $reflection->getAttributes(),
            ),
        );
    }
}
