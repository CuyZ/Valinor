<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Utility\IsSingleton;

/** @internal */
final class MixedType implements Type
{
    use IsSingleton;

    public function accepts(mixed $value): bool
    {
        return true;
    }

    public function matches(Type $other): bool
    {
        return $other instanceof self;
    }

    public function toString(): string
    {
        return 'mixed';
    }
}
