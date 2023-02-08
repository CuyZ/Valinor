<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object\Factory;

use CuyZ\Valinor\Definition\ClassDefinition;
use CuyZ\Valinor\Mapper\Object\ReflectionObjectBuilder;

use function enum_exists;

/** @internal */
final class ReflectionObjectBuilderFactory implements ObjectBuilderFactory
{
    public function for(ClassDefinition $class): array
    {
        if (enum_exists($class->name())) {
            return [];
        }

        return [new ReflectionObjectBuilder($class)];
    }
}
