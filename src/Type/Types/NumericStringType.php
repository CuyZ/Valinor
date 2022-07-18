<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Type\StringType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\Exception\CannotCastValue;
use CuyZ\Valinor\Type\Types\Exception\InvalidNumericStringValue;
use CuyZ\Valinor\Utility\IsSingleton;

use function is_numeric;
use function is_string;

/** @internal */
final class NumericStringType implements StringType
{
    use IsSingleton;

    public function accepts($value): bool
    {
        return is_string($value) && is_numeric($value);
    }

    public function matches(Type $other): bool
    {
        if ($other instanceof UnionType) {
            return $other->isMatchedBy($this);
        }

        return $other instanceof self
            || $other instanceof NativeStringType
            || $other instanceof MixedType;
    }

    public function canCast($value): bool
    {
        return is_string($value)
            || is_numeric($value)
            // @PHP8.0 `$value instanceof Stringable`
            || (is_object($value) && method_exists($value, '__toString'));
    }

    public function cast($value): string
    {
        if (!$this->canCast($value)) {
            throw new CannotCastValue($value, $this);
        }

        $value = (string)$value; // @phpstan-ignore-line

        if (!is_numeric($value)) {
            throw new InvalidNumericStringValue($value);
        }

        return $value;
    }

    public function toString(): string
    {
        return 'numeric-string';
    }
}
