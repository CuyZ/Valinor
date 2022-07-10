<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Type\FixedType;
use CuyZ\Valinor\Type\IntegerType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\Exception\InvalidIntegerValue;

use CuyZ\Valinor\Type\Types\Exception\InvalidIntegerValueType;

use function filter_var;
use function is_bool;

/** @internal */
final class IntegerValueType implements IntegerType, FixedType
{
    private int $value;

    public function __construct(int $value)
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

        if ($other instanceof ArrayKeyType) {
            return $other->isMatchedBy($this);
        }

        if ($other instanceof NativeIntegerType || $other instanceof MixedType) {
            return true;
        }

        if ($other instanceof NegativeIntegerType && $this->value < 0) {
            return true;
        }

        if ($other instanceof PositiveIntegerType && $this->value > 0) {
            return true;
        }

        return false;
    }

    public function canCast($value): bool
    {
        return ! is_bool($value) && filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    public function cast($value): int
    {
        if (! $this->canCast($value)) {
            throw new InvalidIntegerValueType($value, $this->value);
        }

        $value = (int)$value; // @phpstan-ignore-line

        if (! $this->accepts($value)) {
            throw new InvalidIntegerValue($value, $this->value);
        }

        return $value;
    }

    public function value(): int
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return (string)$this->value;
    }
}
