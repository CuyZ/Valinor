<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Utility\IsSingleton;

use function CuyZ\Valinor\Compiler\call;
use function is_object;

/** @internal */
final class UndefinedObjectType implements Type
{
    use IsSingleton;

    public function accepts(mixed $value): bool
    {
        return is_object($value);
    }

    public function compiledAccept(Node $node): Node
    {
        return call('is_object', [$node]);
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

    public function nativeType(): UndefinedObjectType
    {
        return $this;
    }

    public function toString(): string
    {
        return 'object';
    }
}
