<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object;

use CuyZ\Valinor\Definition\ClassDefinition;
use CuyZ\Valinor\Definition\PropertyDefinition;
use CuyZ\Valinor\Mapper\Object\Exception\MissingPropertyArgument;

use function array_key_exists;
use function array_map;
use function array_values;
use function iterator_to_array;

/** @api */
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
        return $this->arguments ??= new Arguments(
            ...array_map(function (PropertyDefinition $property) {
                $argument = $property->hasDefaultValue()
                    ? Argument::optional($property->name(), $property->type(), $property->defaultValue())
                    : Argument::required($property->name(), $property->type());

                return $argument->withAttributes($property->attributes());
            }, array_values(iterator_to_array($this->class->properties()))) // @PHP8.1 array unpacking
        );
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

        (function () use ($arguments): void {
            foreach ($arguments as $name => $value) {
                $this->{$name} = $value; // @phpstan-ignore-line
            }
        })->call($object);

        return $object;
    }
}
