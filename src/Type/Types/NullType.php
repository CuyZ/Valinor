<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Utility\IsSingleton;

use function CuyZ\Valinor\Compiler\value;

/** @internal */
final class NullType implements Type
{
    use IsSingleton;

    public function accepts(mixed $value): bool
    {
        return $value === null;
    }

    public function compiledAccept(Node $node): Node
    {
        return $node->equals(value(null));
    }

    public function matches(Type $other): bool
    {
        if ($other instanceof UnionType) {
            return $other->isMatchedBy($this);
        }

        return $other instanceof self
            || $other instanceof MixedType;
    }

    public function inferGenericsFrom(Type $other, Generics $generics): Generics
    {
        return $generics;
    }

    public function nativeType(): Type
    {
        return $this;
    }

    public function toString(): string
    {
        return 'null';
    }
}
