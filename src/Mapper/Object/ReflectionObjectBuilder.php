<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object;

use CuyZ\Valinor\Definition\ClassDefinition;
use CuyZ\Valinor\Mapper\Object\Exception\MissingPropertyArgument;

use function array_key_exists;

/** @api */
final class ReflectionObjectBuilder implements ObjectBuilder
{
    private ClassDefinition $class;

    public function __construct(ClassDefinition $class)
    {
        $this->class = $class;
    }

    public function describeArguments(): iterable
    {
        foreach ($this->class->properties() as $property) {
            $argument = $property->hasDefaultValue()
                ? Argument::optional($property->name(), $property->type(), $property->defaultValue())
                : Argument::required($property->name(), $property->type());

            yield $argument->withAttributes($property->attributes());
        }
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
