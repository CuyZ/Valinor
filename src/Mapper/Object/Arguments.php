<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object;

use Countable;
use CuyZ\Valinor\Definition\ParameterDefinition;
use CuyZ\Valinor\Definition\Parameters;
use CuyZ\Valinor\Definition\Properties;
use CuyZ\Valinor\Definition\PropertyDefinition;
use CuyZ\Valinor\Type\Types\ShapedArrayElement;
use CuyZ\Valinor\Type\Types\ShapedArrayType;
use CuyZ\Valinor\Type\Types\StringValueType;
use IteratorAggregate;
use Traversable;

use function array_diff_key;
use function array_keys;
use function array_map;
use function array_values;
use function count;

/**
 * @internal
 *
 * @implements IteratorAggregate<Argument>
 */
final readonly class Arguments implements IteratorAggregate, Countable
{
    /** @var array<string, Argument> */
    private array $arguments;

    private ShapedArrayType $shapedArray;

    public function __construct(Argument ...$arguments)
    {
        $args = [];
        foreach ($arguments as $argument) {
            $args[$argument->name()] = $argument;
        }
        $this->arguments = $args;
        $this->shapedArray = new ShapedArrayType(
            elements: array_map(
                static fn (Argument $argument) => new ShapedArrayElement(
                    key: new StringValueType($argument->name()),
                    type: $argument->type(),
                    optional: ! $argument->isRequired(),
                    attributes: $argument->attributes(),
                ),
                $this->arguments,
            ),
            isUnsealed: false,
            unsealedType: null,
        );
    }

    public static function fromParameters(Parameters $parameters): self
    {
        return new self(...array_map(
            fn (ParameterDefinition $parameter) => Argument::fromParameter($parameter),
            [...$parameters],
        ));
    }

    public static function fromProperties(Properties $properties): self
    {
        return new self(...array_map(
            fn (PropertyDefinition $property) => Argument::fromProperty($property),
            [...$properties],
        ));
    }

    public function at(int $index): Argument
    {
        return array_values($this->arguments)[$index];
    }

    /**
     * @return list<string>
     */
    public function names(): array
    {
        return array_keys($this->arguments);
    }

    public function merge(self $other): self
    {
        return new self(
            ...$this->arguments,
            ...array_diff_key($other->arguments, $this->arguments)
        );
    }

    public function toShapedArray(): ShapedArrayType
    {
        return $this->shapedArray;
    }

    /**
     * @return array<string, Argument>
     */
    public function toArray(): array
    {
        return $this->arguments;
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
