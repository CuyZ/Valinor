<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition;

use Countable;
use CuyZ\Valinor\Definition\Exception\MethodNotFound;
use IteratorAggregate;
use Traversable;

/**
 * @implements IteratorAggregate<string, MethodDefinition>
 */
final class Methods implements IteratorAggregate, Countable
{
    /** @var MethodDefinition[] */
    private array $methods = [];

    public function __construct(MethodDefinition ...$methods)
    {
        foreach ($methods as $method) {
            $this->methods[$method->name()] = $method;
        }
    }

    public function has(string $name): bool
    {
        return isset($this->methods[$name]);
    }

    public function get(string $name): MethodDefinition
    {
        if (! $this->has($name)) {
            throw new MethodNotFound($name);
        }

        return $this->methods[$name];
    }

    public function hasConstructor(): bool
    {
        return $this->has('__construct');
    }

    public function constructor(): MethodDefinition
    {
        return $this->get('__construct');
    }

    public function count(): int
    {
        return count($this->methods);
    }

    /**
     * @return Traversable<string, MethodDefinition>
     */
    public function getIterator(): Traversable
    {
        yield from $this->methods;
    }
}
