<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fake\Definition;

use CuyZ\Valinor\Definition\Attributes;
use Traversable;

final class FakeNonEmptyAttributes implements Attributes
{
    public function has(string $className): bool
    {
        return true;
    }

    public function ofType(string $className): iterable
    {
        return [];
    }

    public function count(): int
    {
        return 1;
    }

    public function getIterator(): Traversable
    {
        yield from [];
    }
}
