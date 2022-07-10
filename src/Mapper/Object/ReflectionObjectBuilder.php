<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object;

use CuyZ\Valinor\Definition\ClassDefinition;
use CuyZ\Valinor\Mapper\Object\Exception\MissingPropertyArgument;

use function array_key_exists;
use function count;

/** @internal */
final class ReflectionObjectBuilder implements ObjectBuilder
{
    private ClassDefinition $class;

    private Arguments $arguments;

    public function __construct(ClassDefinition $class)
    {
        $this->class = $class;
    }

    public function describeArguments(): Arguments
    {
        return $this->arguments ??= Arguments::fromProperties($this->class->properties());
    }

    public function build(array $arguments): object
    {
        foreach ($this->class->properties() as $property) {
            if (! array_key_exists($property->name(), $arguments) && ! $property->hasDefaultValue()) {
                throw new MissingPropertyArgument($property);
            }
        }

        // @PHP8.0 `$object = new ($this->class->name())();`
        $className = $this->class->name();
        $object = new $className();

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
        return $this->class->name() . ' (properties)';
    }
}
