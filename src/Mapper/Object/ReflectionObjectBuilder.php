<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object;

use CuyZ\Valinor\Definition\ClassDefinition;
use CuyZ\Valinor\Definition\Properties;
use CuyZ\Valinor\Definition\PropertyDefinition;

use function array_filter;
use function count;
use function iterator_to_array;

/** @internal */
final class ReflectionObjectBuilder implements ObjectBuilder
{
    private Arguments $arguments;

    public function __construct(private ClassDefinition $class) {}

    public function describeArguments(): Arguments
    {
        if (isset($this->arguments)) {
            return $this->arguments;
        }

        // @infection-ignore-all / PHP8.6 remove (only covered by tests on PHP 8.6)
        $properties = array_filter(
            iterator_to_array($this->class->properties),
            self::isWritable(...),
        );

        return $this->arguments = Arguments::fromProperties(new Properties(...$properties));
    }

    public function buildObject(array $arguments): object
    {
        $object = new ($this->class->name)();

        foreach ($this->class->properties as $property) {
            if ($property->isFixed && self::isWritable($property)) {
                $arguments[$property->name] = $property->fixedValue();
            }
        }

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

    /**
     * Since PHP 8.6, a readonly property can have a default value: it is then
     * initialized when the object is instantiated and cannot be written again.
     */
    private static function isWritable(PropertyDefinition $property): bool
    {
        return ! ($property->isReadonly && $property->hasDefaultValue);
    }
}
