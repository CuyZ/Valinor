<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Type\StringType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\Exception\CannotCastValue;
use CuyZ\Valinor\Type\Types\Exception\InvalidEmptyStringValue;
use CuyZ\Valinor\Utility\IsSingleton;

use function is_numeric;
use function is_object;
use function is_string;
use function method_exists;

/** @api */
final class NonEmptyStringType implements StringType
{
    use IsSingleton;

    public function accepts($value): bool
    {
        return is_string($value) && $value !== '';
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
        if (! $this->canCast($value)) {
            throw new CannotCastValue($value, $this);
        }

        $value = (string)$value; // @phpstan-ignore-line

        if ($value === '') {
            throw new InvalidEmptyStringValue();
        }

        return $value;
    }

    public function __toString(): string
    {
        return 'non-empty-string';
    }
}
