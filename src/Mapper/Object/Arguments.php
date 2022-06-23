<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object;

use Countable;
use CuyZ\Valinor\Definition\ParameterDefinition;
use CuyZ\Valinor\Definition\Parameters;
use CuyZ\Valinor\Definition\Properties;
use CuyZ\Valinor\Definition\PropertyDefinition;
use IteratorAggregate;
use Traversable;

use function array_map;
use function array_values;
use function iterator_to_array;

/**
 * @internal
 *
 * @implements IteratorAggregate<Argument>
 */
final class Arguments implements IteratorAggregate, Countable
{
    /** @var Argument[] */
    private array $arguments;

    public function __construct(Argument ...$arguments)
    {
        $this->arguments = $arguments;
    }

    public static function fromParameters(Parameters $parameters): self
    {
        return new self(...array_map(function (ParameterDefinition $parameter) {
            $argument = $parameter->isOptional()
                ? Argument::optional($parameter->name(), $parameter->type(), $parameter->defaultValue())
                : Argument::required($parameter->name(), $parameter->type());

            return $argument->withAttributes($parameter->attributes());
        }, array_values(iterator_to_array($parameters)))); // @PHP8.1 array unpacking
    }

    public static function fromProperties(Properties $properties): self
    {
        return new self(...array_map(function (PropertyDefinition $property) {
            $argument = $property->hasDefaultValue()
                ? Argument::optional($property->name(), $property->type(), $property->defaultValue())
                : Argument::required($property->name(), $property->type());

            return $argument->withAttributes($property->attributes());
        }, array_values(iterator_to_array($properties)))); // @PHP8.1 array unpacking
    }

    public function at(int $index): Argument
    {
        return $this->arguments[$index];
    }

    public function has(string $name): bool
    {
        foreach ($this->arguments as $argument) {
            if ($argument->name() === $name) {
                return true;
            }
        }

        return false;
    }

    public function count(): int
    {
        return count($this->arguments);
    }

    /**
     * @return Traversable<Argument>
     */
    public function getIterator(): Traversable
    {
        yield from $this->arguments;
    }
}
