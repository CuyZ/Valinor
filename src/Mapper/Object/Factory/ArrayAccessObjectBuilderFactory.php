<?php

namespace CuyZ\Valinor\Mapper\Object\Factory;

use CuyZ\Valinor\Definition\ClassDefinition;
use CuyZ\Valinor\Mapper\Object\ArrayAccessObjectBuilder;
use CuyZ\Valinor\Mapper\Object\ObjectBuilder;

class ArrayAccessObjectBuilderFactory implements ObjectBuilderFactory
{
    private ObjectBuilderFactory $delegate;

    public function __construct(ObjectBuilderFactory $delegate)
    {
        $this->delegate = $delegate;
    }

    public function for(ClassDefinition $class, $source): ObjectBuilder
    {
        if (is_a($class->name(), \ArrayAccess::class, true)) {
            return new ArrayAccessObjectBuilder($class);
        }

        return $this->delegate->for($class, $source);
    }
}
