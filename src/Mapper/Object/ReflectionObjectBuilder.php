<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object;

use CuyZ\Valinor\Definition\ClassDefinition;

use function count;

/** @internal */
final class ReflectionObjectBuilder implements ObjectBuilder
{
    private Arguments $arguments;

    public function __construct(private ClassDefinition $class) {}

    public function describeArguments(): Arguments
    {
        return $this->arguments ??= Arguments::fromProperties($this->class->properties);
    }

    public function buildObject(array $arguments): object
    {
        $object = new ($this->class->name)();

        if (count($arguments) > 0) {
            (function () use ($arguments): void {
                foreach ($arguments as $name => $value) {
                    $this->{$name} = $value; // @phpstan-ignore-line
                }
            })->call($object);
        }

        return $object;
    }

    public function signature(): string
    {
        return $this->class->name . ' (properties)';
    }
}
