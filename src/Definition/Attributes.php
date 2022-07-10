<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition;

use Countable;
use IteratorAggregate;

/**
 * @internal
 *
 * @extends IteratorAggregate<object>
 */
interface Attributes extends IteratorAggregate, Countable
{
    /**
     * @param class-string $className
     */
    public function has(string $className): bool;

    /**
     * @template T of object
     *
     * @param class-string<T> $className
     * @return list<T>
     */
    public function ofType(string $className): array;
}
