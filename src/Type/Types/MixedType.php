<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Utility\IsSingleton;

final class MixedType implements Type
{
    use IsSingleton;

    public function accepts($value): bool
    {
        return true;
    }

    public function matches(Type $other): bool
    {
        return $other instanceof self;
    }

    public function __toString(): string
    {
        return 'mixed';
    }
}
