<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object;

use CuyZ\Valinor\Mapper\Object\Exception\CannotFindObjectBuilder;
use CuyZ\Valinor\Mapper\Object\Exception\SeveralObjectBuildersFound;

/** @internal */
final class ObjectBuilderFilterer
{
    /**
     * @param mixed $source
     */
    public function filter($source, ObjectBuilder ...$builders): ObjectBuilder
    {
        /** @var non-empty-list<ObjectBuilder> $builders */
        if (count($builders) === 1) {
            return $builders[0];
        }

        $constructors = [];

        foreach ($builders as $builder) {
            $filledNumber = $this->filledArguments($builder, $source);

            if ($filledNumber === false) {
                continue;
            }

            $constructors[$filledNumber][] = $builder;
        }

        ksort($constructors);

        $constructorsWithMostArguments = array_pop($constructors) ?: [];

        if (count($constructorsWithMostArguments) === 0) {
            throw new CannotFindObjectBuilder($source, $builders);
        }

        if (count($constructorsWithMostArguments) > 1) {
            throw new SeveralObjectBuildersFound($source);
        }

        return $constructorsWithMostArguments[0];
    }

    /**
     * @PHP8.0 union
     *
     * @param mixed $source
     * @return bool|int<0, max>
     */
    private function filledArguments(ObjectBuilder $builder, $source)
    {
        $arguments = [...$builder->describeArguments()];

        if (! is_array($source)) {
            return count($arguments) === 1;
        }

        /** @infection-ignore-all */
        $filled = 0;

        foreach ($arguments as $parameter) {
            if (isset($source[$parameter->name()])) {
                $filled++;
            } elseif ($parameter->isRequired()) {
                return false;
            }
        }

        /** @var int<0, max> $filled */
        return $filled;
    }
}
