<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object\Factory;

use CuyZ\Valinor\Definition\ClassDefinition;
use CuyZ\Valinor\Mapper\Object\Exception\TooManyObjectBuilderFactoryAttributes;

use function count;

/** @internal */
final class AttributeObjectBuilderFactory implements ObjectBuilderFactory
{
    private ObjectBuilderFactory $delegate;

    public function __construct(ObjectBuilderFactory $delegate)
    {
        $this->delegate = $delegate;
    }

    public function for(ClassDefinition $class): iterable
    {
        $attributes = $class->attributes()->ofType(ObjectBuilderFactory::class);

        if (count($attributes) === 0) {
            return $this->delegate->for($class);
        }

        if (count($attributes) > 1) {
            throw new TooManyObjectBuilderFactoryAttributes($class, $attributes);
        }

        return $attributes[0]->for($class);
    }
}
