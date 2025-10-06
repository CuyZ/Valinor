<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object\Exception;

use CuyZ\Valinor\Definition\ClassDefinition;
use RuntimeException;

/** @internal */
final class CannotInstantiateObject extends RuntimeException
{
    public function __construct(ClassDefinition $class)
    {
        parent::__construct("No available constructor found for class `$class->name`.");
    }
}
