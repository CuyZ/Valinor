<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object;

use CuyZ\Valinor\Definition\Parameters;
use IteratorAggregate;
use Traversable;

use function array_key_exists;
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
            $name = $parameter->name;

            if ($parameter->isVariadic) {
                $this->arguments = [...$this->arguments, ...array_values($arguments[$name])]; // @phpstan-ignore-line we know that the argument is iterable
            } elseif (array_key_exists($name, $arguments)) {
                $this->arguments[] = $arguments[$name];
            }
        }
    }

    public function getIterator(): Traversable
    {
        yield from $this->arguments;
    }
}
