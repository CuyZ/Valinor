<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fake\Definition;

use CuyZ\Valinor\Definition\Attributes;
use CuyZ\Valinor\Definition\ClassDefinition;
use CuyZ\Valinor\Definition\Methods;
use CuyZ\Valinor\Definition\Properties;
use CuyZ\Valinor\Type\Types\NativeClassType;
use ReflectionClass;
use stdClass;

use function array_map;

final class FakeClassDefinition
{
    private function __construct() {}

    /**
     * @param class-string $name
     */
    public static function new(string $name = stdClass::class): ClassDefinition
    {
        return new ClassDefinition(
            $name,
            new NativeClassType($name),
            new Attributes(),
            new Properties(),
            new Methods(),
            true,
            false,
        );
    }

    /**
     * @param ReflectionClass<covariant object> $reflection
     */
    public static function fromReflection(ReflectionClass $reflection): ClassDefinition
    {
        $properties = array_map(
            FakePropertyDefinition::fromReflection(...),
            $reflection->getProperties()
        );

        $methods = array_map(
            FakeMethodDefinition::fromReflection(...),
            $reflection->getMethods()
        );

        return new ClassDefinition(
            $reflection->name,
            new NativeClassType($reflection->name),
            new Attributes(),
            new Properties(...$properties),
            new Methods(...$methods),
            $reflection->isFinal(),
            $reflection->isAbstract(),
        );
    }
}
