<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition;

use Countable;
use IteratorAggregate;
use Traversable;

use function count;

/**
 * @internal
 *
 * @implements IteratorAggregate<string, PropertyDefinition>
 */
final class Properties implements IteratorAggregate, Countable
{
    /**  @var PropertyDefinition[] */
    private array $properties = [];

    public function __construct(PropertyDefinition ...$properties)
    {
        foreach ($properties as $property) {
            $this->properties[$property->name] = $property;
        }
    }

    public function has(string $name): bool
    {
        return isset($this->properties[$name]);
    }

    public function get(string $name): PropertyDefinition
    {
        return $this->properties[$name];
    }

    public function count(): int
    {
        return count($this->properties);
    }

    /**
     * @return Traversable<string, PropertyDefinition>
     */
    public function getIterator(): Traversable
    {
        yield from $this->properties;
    }
}
