<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition;

use Countable;
use CuyZ\Valinor\Type\Types\Generics;
use IteratorAggregate;
use Traversable;

use function array_filter;
use function array_map;
use function array_slice;
use function array_values;
use function count;

/**
 * @internal
 *
 * @implements IteratorAggregate<string, ParameterDefinition>
 */
final class Parameters implements IteratorAggregate, Countable
{
    /** @var array<non-empty-string, ParameterDefinition> */
    private array $parameters = [];

    public function __construct(ParameterDefinition ...$parameters)
    {
        foreach ($parameters as $parameter) {
            $this->parameters[$parameter->name] = $parameter;
        }
    }

    public function has(string $name): bool
    {
        return isset($this->parameters[$name]);
    }

    public function get(string $name): ParameterDefinition
    {
        return $this->parameters[$name];
    }

    /**
     * @param int<0, max> $index
     */
    public function at(int $index): ParameterDefinition
    {
        return array_values($this->parameters)[$index];
    }

    public function shift(): self
    {
        return new self(...array_slice($this->parameters, 1));
    }

    public function assignGenerics(Generics $generics): self
    {
        return new self(
            ...array_map(
                static fn (ParameterDefinition $parameter) => $parameter->assignGenerics($generics),
                $this->parameters,
            ),
        );
    }

    public function withoutFixed(): self
    {
        return new self(...array_filter(
            $this->parameters,
            static fn (ParameterDefinition $parameter) => ! $parameter->isFixed,
        ));
    }

    /**
     * @return array<non-empty-string, ParameterDefinition>
     */
    public function toArray(): array
    {
        return $this->parameters;
    }

    public function count(): int
    {
        return count($this->parameters);
    }

    public function forCallable(callable $callable): self
    {
        return new self(...array_map(
            static fn (ParameterDefinition $parameter) => $parameter->forCallable($callable),
            $this->parameters
        ));
    }

    /**
     * @return Traversable<string, ParameterDefinition>
     */
    public function getIterator(): Traversable
    {
        yield from $this->parameters;
    }
}
