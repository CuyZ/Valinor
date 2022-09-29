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
use function is_object;
use function is_string;
use function method_exists;

/** @internal */
final class NativeStringType implements StringType
{
    use IsSingleton;

    public function accepts($value): bool
    {
        return is_string($value);
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
        return is_string($value)
            || is_numeric($value)
            // @PHP8.0 `$value instanceof Stringable`
            || (is_object($value) && method_exists($value, '__toString'));
    }

    public function cast($value): string
    {
        assert($this->canCast($value));

        return (string)$value; // @phpstan-ignore-line
    }

    public function errorMessage(): ErrorMessage
    {
        return MessageBuilder::newError('Value {source_value} is not a valid string.')->build();
    }

    public function toString(): string
    {
        return 'string';
    }
}
