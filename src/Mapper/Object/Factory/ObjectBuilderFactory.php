<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object\Factory;

use CuyZ\Valinor\Definition\ClassDefinition;
use CuyZ\Valinor\Mapper\Object\ObjectBuilder;

/** @internal */
interface ObjectBuilderFactory
{
    /**
     * @return non-empty-list<ObjectBuilder>
     */
    public function for(ClassDefinition $class): array;
}
