<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Type\IntegerType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\Exception\CannotCastValue;
use CuyZ\Valinor\Type\Types\Exception\InvalidPositiveIntegerValue;
use CuyZ\Valinor\Utility\IsSingleton;

use function filter_var;
use function is_bool;
use function is_int;

/** @internal */
final class PositiveIntegerType implements IntegerType
{
    use IsSingleton;

    public function accepts($value): bool
    {
        return is_int($value) && $value > 0;
    }

    public function matches(Type $other): bool
    {
        if ($other instanceof UnionType) {
            return $other->isMatchedBy($this);
        }

        return $other instanceof self
            || $other instanceof NativeIntegerType
            || $other instanceof MixedType;
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

        if ($value <= 0) {
            throw new InvalidPositiveIntegerValue($value);
        }

        return $value;
    }

    public function __toString(): string
    {
        return 'positive-int';
    }
}
