<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Type\Type;

use function is_array;

/** @internal */
final class EmptyArrayType implements Type
{
    private static self $arr;

    public function __construct()
    {
    }

    public static function get(): self
    {
        return self::$arr ??= new self();
    }

    public function accepts(mixed $value): bool
    {
        if (! is_array($value)) {
            return false;
        }

        return count($value) === 0;
    }

    public function matches(Type $other): bool
    {
        return $other instanceof self;
    }

    public function toString(): string
    {
        return 'array<never, never>';
    }
}
