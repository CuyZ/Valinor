<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fake\Definition;

use CuyZ\Valinor\Definition\Attributes;
use Traversable;

final class FakeAttributes implements Attributes
{
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
