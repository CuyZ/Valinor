<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object\Exception;

use CuyZ\Valinor\Definition\ClassDefinition;
use CuyZ\Valinor\Mapper\Object\Factory\ObjectBuilderFactory;
use RuntimeException;

use function count;

/** @internal */
final class TooManyObjectBuilderFactoryAttributes extends RuntimeException
{
    /**
     * @param ObjectBuilderFactory[] $attributes
     */
    public function __construct(ClassDefinition $class, array $attributes)
    {
        $factoryClass = ObjectBuilderFactory::class;
        $count = count($attributes);

        parent::__construct(
            "Only one attribute of type `$factoryClass` is allowed, class `{$class->name()}` contains $count.",
            1_634_044_714
        );
    }
}
