<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object\Factory;

use CuyZ\Valinor\Definition\ClassDefinition;
use CuyZ\Valinor\Mapper\Object\Exception\ObjectBuildersCollision;

use function array_shift;
use function count;
use function current;
use function next;

/** @internal */
final class CollisionObjectBuilderFactory implements ObjectBuilderFactory
{
    public function __construct(private ObjectBuilderFactory $delegate)
    {
    }

    public function for(ClassDefinition $class): array
    {
        $builders = $this->delegate->for($class);

        $sortedBuilders = [];

        foreach ($builders as $builder) {
            $sortedBuilders[count($builder->describeArguments())][] = $builder;
        }

        foreach ($sortedBuilders as $argumentsCount => $buildersList) {
            if (count($buildersList) <= 1) {
                continue;
            }

            if ($argumentsCount <= 1) {
                throw new ObjectBuildersCollision($class, ...$buildersList);
            }

            // @phpstan-ignore-next-line // false positive
            while (($current = array_shift($buildersList)) && count($buildersList) > 0) {
                $arguments = $current->describeArguments();

                do {
                    $other = current($buildersList);

                    $collisions = 0;

                    foreach ($arguments as $argumentA) {
                        $name = $argumentA->name();

                        foreach ($other->describeArguments() as $argumentB) {
                            if ($argumentB->name() === $name) {
                                $collisions++;
                                // @infection-ignore-all
                                break;
                            }
                        }
                    }

                    if ($collisions >= count($arguments)) {
                        throw new ObjectBuildersCollision($class, $current, $other);
                    }
                } while (next($buildersList));
            }
        }

        return $builders;
    }
}
