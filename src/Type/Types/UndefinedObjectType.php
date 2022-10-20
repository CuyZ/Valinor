<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Type\ObjectType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Utility\IsSingleton;

use function is_object;

/** @internal */
final class UndefinedObjectType implements Type
{
    use IsSingleton;

    public function accepts(mixed $value): bool
    {
        return is_object($value);
    }

    public function matches(Type $other): bool
    {
        if ($other instanceof UnionType) {
            return $other->isMatchedBy($this);
        }

        return $other instanceof self
            || $other instanceof ObjectType
            || $other instanceof IntersectionType
            || $other instanceof MixedType;
    }

    public function toString(): string
    {
        return 'object';
    }
}
