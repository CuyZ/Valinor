<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object\Exception;

use CuyZ\Valinor\Definition\ClassDefinition;
use CuyZ\Valinor\Mapper\Object\Factory\SuitableObjectBuilderNotFound;
use RuntimeException;

/** @internal */
final class CannotInstantiateObject extends RuntimeException implements SuitableObjectBuilderNotFound
{
    public function __construct(ClassDefinition $class)
    {
        parent::__construct(
            "No available constructor found for class `{$class->name()}`.",
            1_646_916_477
        );
    }
}
