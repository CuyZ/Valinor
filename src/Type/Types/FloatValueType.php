<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Type\FixedType;
use CuyZ\Valinor\Type\FloatType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\Exception\InvalidFloatValue;
use CuyZ\Valinor\Type\Types\Exception\InvalidFloatValueType;

/** @internal */
final class FloatValueType implements FloatType, FixedType
{
    private float $value;

    public function __construct(float $value)
    {
        $this->value = $value;
    }

    public function accepts($value): bool
    {
        return $value === $this->value;
    }

    public function matches(Type $other): bool
    {
        if ($other instanceof UnionType) {
            return $other->isMatchedBy($this);
        }

        if ($other instanceof self) {
            return $this->value === $other->value;
        }

        return $other instanceof NativeFloatType || $other instanceof MixedType;
    }

    public function canCast($value): bool
    {
        return is_numeric($value);
    }

    public function cast($value): float
    {
        if (! $this->canCast($value)) {
            throw new InvalidFloatValueType($value, $this->value);
        }

        $value = (float)$value; // @phpstan-ignore-line

        if (! $this->accepts($value)) {
            throw new InvalidFloatValue($value, $this->value);
        }

        return $value;
    }

    public function value(): float
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return (string)$this->value;
    }
}
