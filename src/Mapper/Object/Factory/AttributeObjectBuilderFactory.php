<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object\Factory;

use CuyZ\Valinor\Definition\ClassDefinition;
use CuyZ\Valinor\Mapper\Object\Exception\TooManyObjectBuilderFactoryAttributes;
use CuyZ\Valinor\Mapper\Object\ObjectBuilder;

use function count;

/** @internal */
final class AttributeObjectBuilderFactory implements ObjectBuilderFactory
{
    private ObjectBuilderFactory $delegate;

    public function __construct(ObjectBuilderFactory $delegate)
    {
        $this->delegate = $delegate;
    }

    public function for(ClassDefinition $class, $source): ObjectBuilder
    {
        /** @var ObjectBuilderFactory[] $attributes */
        $attributes = $class->attributes()->ofType(ObjectBuilderFactory::class);

        if (count($attributes) === 0) {
            return $this->delegate->for($class, $source);
        }

        if (count($attributes) > 1) {
            throw new TooManyObjectBuilderFactoryAttributes($class, $attributes);
        }

        return \reset($attributes)->for($class, $source);
    }
}
