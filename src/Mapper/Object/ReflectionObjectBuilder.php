<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object;

use CuyZ\Valinor\Definition\ClassDefinition;
use CuyZ\Valinor\Mapper\Object\Exception\InvalidSourceForObject;
use CuyZ\Valinor\Mapper\Object\Exception\MissingPropertyArgument;

use function array_key_exists;
use function array_keys;
use function count;
use function is_array;
use function iterator_to_array;

final class ReflectionObjectBuilder implements ObjectBuilder
{
    private ClassDefinition $class;

    public function __construct(ClassDefinition $class)
    {
        $this->class = $class;
    }

    public function describeArguments($source): iterable
    {
        $source = $this->transformSource($source);

        foreach ($this->class->properties() as $property) {
            $name = $property->name();
            $type = $property->type();
            $attributes = $property->attributes();
            $value = array_key_exists($name, $source) ? $source[$name] : $property->defaultValue();

            yield new Argument($name, $type, $value, $attributes);
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

    /**
     * @param mixed $source
     * @return mixed[]
     */
    private function transformSource($source): array
    {
        if ($source === null) {
            return [];
        }

        $properties = $this->class->properties();

        if (count($properties) === 1) {
            $name = array_keys(iterator_to_array($properties))[0];

            if (! is_array($source) || ! array_key_exists($name, $source)) {
                $source = [$name => $source];
            }
        }

        if (! is_array($source)) {
            throw new InvalidSourceForObject($source);
        }

        return $source;
    }
}
