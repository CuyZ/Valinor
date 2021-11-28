<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object\Factory;

use CuyZ\Valinor\Definition\ClassDefinition;
use CuyZ\Valinor\Mapper\Object\DateTimeObjectBuilder;
use CuyZ\Valinor\Mapper\Object\MethodObjectBuilder;
use CuyZ\Valinor\Mapper\Object\ObjectBuilder;
use CuyZ\Valinor\Mapper\Object\ReflectionObjectBuilder;
use DateTimeInterface;

use function is_a;

final class BasicObjectBuilderFactory implements ObjectBuilderFactory
{
    public function for(ClassDefinition $class): ObjectBuilder
    {
        if (is_a($class->name(), DateTimeInterface::class, true)) {
            return new DateTimeObjectBuilder($class->name());
        }

        if ($class->methods()->hasConstructor()) {
            return new MethodObjectBuilder($class, '__construct');
        }

        return new ReflectionObjectBuilder($class);
    }
}
