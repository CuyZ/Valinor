<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object;

use CuyZ\Valinor\Definition\Parameters;
use CuyZ\Valinor\Mapper\Object\Exception\MissingMethodArgument;
use IteratorAggregate;
use Traversable;

use function array_values;

/**
 * @internal
 *
 * @implements IteratorAggregate<mixed>
 */
final class MethodArguments implements IteratorAggregate
{
    /** @var list<mixed> */
    private array $arguments = [];

    /**
     * @param array<string, mixed> $arguments
     */
    public function __construct(Parameters $parameters, array $arguments)
    {
        foreach ($parameters as $parameter) {
            $name = $parameter->name();

            if (! array_key_exists($parameter->name(), $arguments) && ! $parameter->isOptional()) {
                throw new MissingMethodArgument($parameter);
            }

            if ($parameter->isVariadic()) {
                // @PHP8.0 remove `array_values`? Behaviour might change, careful.
                $this->arguments = [...$this->arguments, ...array_values($arguments[$name])]; // @phpstan-ignore-line we know that the argument is iterable
            } else {
                $this->arguments[] = $arguments[$name];
            }
        }
    }

    public function getIterator(): Traversable
    {
        // @PHP8.0 `array_values` can be removed
        yield from array_values($this->arguments);
    }
}
