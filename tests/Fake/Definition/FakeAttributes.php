<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fake\Definition;

use CuyZ\Valinor\Definition\Attributes;
use Traversable;

final class FakeAttributes implements Attributes
{
    private int $count = 0;

    public static function notEmpty(): self
    {
        $self = new self();
        $self->count = 1;

        return $self;
    }

    public function has(string $className): bool
    {
        return false;
    }

    public function ofType(string $className): array
    {
        return [];
    }

    public function count(): int
    {
        return $this->count;
    }

    public function getIterator(): Traversable
    {
        yield from [];
    }
}
