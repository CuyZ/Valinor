<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object\Factory;

use CuyZ\Valinor\Definition\ClassDefinition;
use CuyZ\Valinor\Mapper\Object\ObjectBuilder;

/** @internal */
interface ObjectBuilderFactory
{
    /**
     * @param mixed $source
     *
     * @throws SuitableObjectBuilderNotFound
     */
    public function for(ClassDefinition $class, $source): ObjectBuilder;
}
