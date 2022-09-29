<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use CuyZ\Valinor\Mapper\Tree\Message\MessageBuilder;
use CuyZ\Valinor\Type\StringType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Utility\IsSingleton;

use function assert;
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
            || $other instanceof NonEmptyStringType
            || $other instanceof MixedType;
    }

    public function canCast($value): bool
    {
        // @PHP8.0 `$value instanceof Stringable`
        if (is_object($value) && method_exists($value, '__toString')) {
            $value = (string)$value;
        }

        return is_numeric($value);
    }

    public function cast($value): string
    {
        assert($this->canCast($value));

        return (string)$value; // @phpstan-ignore-line
    }

    public function errorMessage(): ErrorMessage
    {
        return MessageBuilder::newError('Value {source_value} is not a valid numeric string.')->build();
    }

    public function toString(): string
    {
        return 'numeric-string';
    }
}
