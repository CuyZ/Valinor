<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object\Factory;

use CuyZ\Valinor\Definition\ClassDefinition;
use CuyZ\Valinor\Mapper\Object\Exception\ObjectBuildersCollision;
use CuyZ\Valinor\Mapper\Object\ObjectBuilder;
use CuyZ\Valinor\Type\ScalarType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Utility\TypeHelper;

use function array_intersect_key;
use function array_keys;
use function array_merge;
use function count;
use function krsort;
use function usort;

/** @internal */
final class SortingObjectBuilderFactory implements ObjectBuilderFactory
{
    public function __construct(private ObjectBuilderFactory $delegate) {}

    /**
     * Will properly sort the object builders, based on the number of arguments
     * they need, and on the types of the arguments.
     *
     * Builders with most parameters will be prioritized, and in the case of
     * parameters' name collision, the builder with the most specific types will
     * be prioritized.
     *
     * Type priority is as follows:
     * 1. Non-scalar type
     * 2. Integer type
     * 3. Float type
     * 4. String type
     * 5. Boolean type
     */
    public function for(ClassDefinition $class): array
    {
        $builders = $this->delegate->for($class);

        $sortedByArgumentsNumber = [];
        $sortedByPriority = [];

        foreach ($builders as $builder) {
            $sortedByArgumentsNumber[$builder->describeArguments()->count()][] = $builder;
        }

        krsort($sortedByArgumentsNumber);

        foreach ($sortedByArgumentsNumber as $sortedBuilders) {
            usort($sortedBuilders, $this->sortObjectBuilders(...));

            $sortedByPriority = array_merge($sortedByPriority, $sortedBuilders);
        }

        return $sortedByPriority;
    }

    private function sortObjectBuilders(ObjectBuilder $builderA, ObjectBuilder $builderB): int
    {
        $argumentsA = $builderA->describeArguments()->toArray();
        $argumentsB = $builderB->describeArguments()->toArray();

        $sharedArguments = array_keys(array_intersect_key($argumentsA, $argumentsB));

        $winner = null;

        foreach ($sharedArguments as $name) {
            $typeA = $argumentsA[$name]->type();
            $typeB = $argumentsB[$name]->type();

            $score = $this->sortTypes($typeA, $typeB);

            if ($score === 0) {
                continue;
            }

            $newWinner = $score === 1 ? $builderB : $builderA;

            if ($winner && $winner !== $newWinner) {
                throw new ObjectBuildersCollision($builderA, $builderB);
            }

            $winner = $newWinner;
        }

        if ($winner === null && count($sharedArguments) === count($argumentsA)) {
            throw new ObjectBuildersCollision($builderA, $builderB);
        }

        // @infection-ignore-all / Incrementing or decrementing sorting value makes no sense, so we ignore it.
        return $winner === $builderA ? -1 : 1;
    }

    private function sortTypes(Type $typeA, Type $typeB): int
    {
        if ($typeA instanceof ScalarType && $typeB instanceof ScalarType) {
            return TypeHelper::scalarTypePriority($typeB) <=> TypeHelper::scalarTypePriority($typeA);
        }

        if (! $typeA instanceof ScalarType) {
            // @infection-ignore-all / Decrementing sorting value makes no sense, so we ignore it.
            return -1;
        }

        return 1;
    }
}
