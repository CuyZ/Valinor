<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Compiler\Native\CompliantNode;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Utility\IsSingleton;

/** @internal */
final class NullType implements Type
{
    use IsSingleton;

    public function accepts(mixed $value): bool
    {
        return $value === null;
    }

    public function compiledAccept(CompliantNode $node): CompliantNode
    {
        return $node->equals(Node::value(null));
    }

    public function matches(Type $other): bool
    {
        if ($other instanceof UnionType) {
            return $other->isMatchedBy($this);
        }

        return $other instanceof self
            || $other instanceof MixedType;
    }

    public function toString(): string
    {
        return 'null';
    }
}
