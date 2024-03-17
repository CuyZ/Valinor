<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object;

use CuyZ\Valinor\Mapper\Object\Exception\CannotFindObjectBuilder;
use CuyZ\Valinor\Mapper\Object\Exception\SeveralObjectBuildersFound;

use function count;
use function is_array;
use function reset;

/** @internal */
final class FilteredObjectBuilder implements ObjectBuilder
{
    private ObjectBuilder $delegate;

    private function __construct(mixed $source, ObjectBuilder ...$builders)
    {
        $this->delegate = $this->filterBuilder($source, ...$builders);
    }

    public static function from(mixed $source, ObjectBuilder ...$builders): ObjectBuilder
    {
        if (count($builders) === 1) {
            return $builders[0];
        }

        return new self($source, ...$builders);
    }

    public function describeArguments(): Arguments
    {
        return $this->delegate->describeArguments();
    }

    public function build(array $arguments): object
    {
        return $this->delegate->build($arguments);
    }

    public function signature(): string
    {
        return $this->delegate->signature();
    }

    private function filterBuilder(mixed $source, ObjectBuilder ...$builders): ObjectBuilder
    {
        if (count($builders) === 1) {
            return reset($builders);
        }

        /** @var non-empty-list<ObjectBuilder> $builders */
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
            throw new CannotFindObjectBuilder($builders);
        }

        if (count($constructorsWithMostArguments) > 1) {
            throw new SeveralObjectBuildersFound();
        }

        return $constructorsWithMostArguments[0];
    }

    /**
     * @return false|int<0, max>
     */
    private function filledArguments(ObjectBuilder $builder, mixed $source): false|int
    {
        $arguments = $builder->describeArguments();

        if (! is_array($source)) {
            return count($arguments) === 1 ? 1 : false;
        }

        /** @infection-ignore-all */
        $filled = 0;

        foreach ($arguments as $argument) {
            if (isset($source[$argument->name()])) {
                $filled++;
            } elseif ($argument->isRequired()) {
                return false;
            }
        }

        return $filled;
    }
}
