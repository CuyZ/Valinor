<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use CuyZ\Valinor\Mapper\Tree\Message\MessageBuilder;
use CuyZ\Valinor\Type\FloatType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Utility\IsSingleton;

use function CuyZ\Valinor\Compiler\call;
use function is_float;

/** @internal */
final class NativeFloatType implements FloatType
{
    use IsSingleton;

    public function accepts(mixed $value): bool
    {
        return is_float($value);
    }

    public function compiledAccept(Node $node): Node
    {
        return call('is_float', [$node]);
    }

    public function matches(Type $other): bool
    {
        if ($other instanceof UnionType) {
            return $other->isMatchedBy($this);
        }

        return $other instanceof self
            || $other instanceof ScalarConcreteType
            || $other instanceof MixedType;
    }

    public function inferGenericsFrom(Type $other, Generics $generics): Generics
    {
        return $generics;
    }

    public function errorMessage(): ErrorMessage
    {
        return MessageBuilder::newError('Value {source_value} is not a valid float.')
            ->withCode('invalid_float')
            ->build();
    }

    public function nativeType(): NativeFloatType
    {
        return $this;
    }

    public function toString(): string
    {
        return 'float';
    }
}
