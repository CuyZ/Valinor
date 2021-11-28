<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Type\ScalarType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\Exception\CannotCastValue;
use CuyZ\Valinor\Utility\IsSingleton;

use function is_bool;

final class BooleanType implements ScalarType
{
    use IsSingleton;

    public function accepts($value): bool
    {
        return is_bool($value);
    }

    public function matches(Type $other): bool
    {
        if ($other instanceof UnionType) {
            return $other->isMatchedBy($this);
        }

        return $other instanceof self
            || $other instanceof MixedType;
    }

    public function canCast($value): bool
    {
        return is_bool($value)
            || $value === '1'
            || $value === '0'
            || $value === 1
            || $value === 0
            || $value === 'true'
            || $value === 'false';
    }

    public function cast($value): bool
    {
        if (! $this->canCast($value)) {
            throw new CannotCastValue($value, $this);
        }

        if ($value === 'false') {
            return false;
        }

        return (bool)$value;
    }

    public function __toString(): string
    {
        return 'bool';
    }
}
