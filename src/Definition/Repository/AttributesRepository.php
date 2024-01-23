<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Repository;

use CuyZ\Valinor\Definition\AttributeDefinition;
use ReflectionAttribute;

/** @internal */
interface AttributesRepository
{
    /**
     * @param ReflectionAttribute<object> $reflection
     */
    public function for(ReflectionAttribute $reflection): AttributeDefinition;
}
