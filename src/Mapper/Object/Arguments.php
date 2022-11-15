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
        return new self(...array_map(
            fn (ParameterDefinition $parameter) => Argument::fromParameter($parameter),
            array_values(iterator_to_array($parameters)) // PHP8.1 array unpacking
        ));
    }

    public static function fromProperties(Properties $properties): self
    {
        return new self(...array_map(
            fn (PropertyDefinition $property) => Argument::fromProperty($property),
            array_values(iterator_to_array($properties)) // PHP8.1 array unpacking
        ));
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
