<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Utility\Priority;

use IteratorAggregate;
use Traversable;

/**
 * @api
 *
 * @template T of object
 * @implements IteratorAggregate<T>
 */
final class PrioritizedList implements IteratorAggregate
{
    /** @var array<int, T[]> */
    private array $objects = [];

    /**
     * @param T ...$objects
     */
    public function __construct(object ...$objects)
    {
        foreach ($objects as $object) {
            $this->objects[$this->priority($object)][] = $object;
        }

        krsort($this->objects, SORT_NUMERIC);
    }

    /**
     * @return Traversable<T>
     */
    public function getIterator(): Traversable
    {
        foreach ($this->objects as $priority => $objects) {
            foreach ($objects as $object) {
                yield $priority => $object;
            }
        }
    }

    private function priority(object $object): int
    {
        return $object instanceof HasPriority
            ? $object->priority()
            : 0;
    }
}
