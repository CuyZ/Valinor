<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fixture\Object;

use Stringable;

final class StringableObject implements Stringable
{
    public function __construct(private string $value = 'foo') {}

    public function __toString(): string
    {
        return $this->value;
    }
}
