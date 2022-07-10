<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fake\Type;

use CuyZ\Valinor\Type\FixedType;
use CuyZ\Valinor\Type\Type;

final class FakeFixedType implements FixedType
{
    private string $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function accepts($value): bool
    {
        return $value === $this->value;
    }

    public function matches(Type $other): bool
    {
        return $other instanceof self && $other->value === $this->value;
    }

    public function toString(): string
    {
        return $this->value;
    }
}
