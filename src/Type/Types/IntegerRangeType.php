<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Type\IntegerType;
use CuyZ\Valinor\Type\Parser\Exception\Scalar\ReversedValuesForIntegerRange;
use CuyZ\Valinor\Type\Parser\Exception\Scalar\SameValueForIntegerRange;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\Exception\CannotCastValue;
use CuyZ\Valinor\Type\Types\Exception\InvalidIntegerRangeValue;

use function sprintf;

/** @internal */
final class IntegerRangeType implements IntegerType
{
    private int $min;

    private int $max;

    private string $signature;

    public function __construct(int $min, int $max)
    {
        $this->min = $min;
        $this->max = $max;

        $this->signature = sprintf(
            'int<%s, %s>',
            $min > PHP_INT_MIN ? $min : 'min',
            $max < PHP_INT_MAX ? $max : 'max'
        );

        if ($min === $max) {
            throw new SameValueForIntegerRange($min);
        }

        if ($min > $max) {
            throw new ReversedValuesForIntegerRange($min, $max);
        }
    }

    public function accepts($value): bool
    {
        return is_int($value)
            && $value >= $this->min
            && $value <= $this->max;
    }

    public function matches(Type $other): bool
    {
        if ($other instanceof UnionType) {
            return $other->isMatchedBy($this);
        }

        if ($other instanceof NativeIntegerType || $other instanceof MixedType) {
            return true;
        }

        if ($other instanceof IntegerValueType && $this->accepts($other->value())) {
            return true;
        }

        if ($other instanceof NegativeIntegerType && $this->min < 0 && $this->max < 0) {
            return true;
        }

        if ($other instanceof PositiveIntegerType && $this->min > 0 && $this->max > 0) {
            return true;
        }

        if ($other instanceof self) {
            return $other->min === $this->min && $other->max === $this->max;
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
            throw new CannotCastValue($value, $this);
        }

        $value = (int)$value; // @phpstan-ignore-line

        if ($value < $this->min || $value > $this->max) {
            throw new InvalidIntegerRangeValue($value, $this);
        }

        return $value;
    }

    public function min(): int
    {
        return $this->min;
    }

    public function max(): int
    {
        return $this->max;
    }

    public function toString(): string
    {
        return $this->signature;
    }
}
