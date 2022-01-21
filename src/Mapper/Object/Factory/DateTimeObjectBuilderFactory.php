<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object\Factory;

use CuyZ\Valinor\Definition\ClassDefinition;
use CuyZ\Valinor\Mapper\Object\DateTimeObjectBuilder;
use CuyZ\Valinor\Mapper\Object\ObjectBuilder;
use DateTimeInterface;

use function is_a;

/** @internal */
final class DateTimeObjectBuilderFactory implements ObjectBuilderFactory
{
    private ObjectBuilderFactory $delegate;

    public function __construct(ObjectBuilderFactory $delegate)
    {
        $this->delegate = $delegate;
    }

    public function for(ClassDefinition $class, $source): ObjectBuilder
    {
        if (is_a($class->name(), DateTimeInterface::class, true)) {
            return new DateTimeObjectBuilder($class->name());
        }

        return $this->delegate->for($class, $source);
    }
}
