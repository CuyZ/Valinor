<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition;

use CuyZ\Valinor\Utility\IsSingleton;
use Traversable;

/** @internal */
final class EmptyAttributes implements Attributes
{
    use IsSingleton;

    public function has(string $className): bool
    {
        return false;
    }

    public function ofType(string $className): iterable
    {
        return [];
    }

    public function count(): int
    {
        return 0;
    }

    public function getIterator(): Traversable
    {
        yield from [];
    }
}
