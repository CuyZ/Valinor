<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition;

use Countable;
use IteratorAggregate;
use Traversable;

use function array_values;

/**
 * @internal
 *
 * @implements IteratorAggregate<string, ParameterDefinition>
 */
final class Parameters implements IteratorAggregate, Countable
{
    /** @var ParameterDefinition[] */
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

    /**
     * @return list<ParameterDefinition>
     */
    public function toList(): array
    {
        return array_values($this->parameters);
    }

    public function count(): int
    {
        return count($this->parameters);
    }

    /**
     * @return Traversable<string, ParameterDefinition>
     */
    public function getIterator(): Traversable
    {
        yield from $this->parameters;
    }
}
