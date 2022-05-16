<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fixture\Object;

// @PHP8.0 implement Stringable
final class StringableObject
{
    private string $value;

    public function __construct(string $value = 'foo')
    {
        $this->value = $value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
