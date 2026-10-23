<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use CuyZ\Valinor\Mapper\Tree\Message\MessageBuilder;
use CuyZ\Valinor\Type\IntegerType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Utility\IsSingleton;

use function CuyZ\Valinor\Compiler\{call, value};
use function is_int;

/** @internal */
final class PositiveIntegerType implements IntegerType
{
    use IsSingleton;

    public function accepts(mixed $value): bool
    {
        return is_int($value) && $value > 0;
    }

    public function compiledAccept(Node $node): Node
    {
        return call('is_int', [$node])->and($node->isGreaterThan(value(0)));
    }

    public function matches(Type $other): bool
    {
        if ($other instanceof UnionType) {
            return $other->isMatchedBy($this);
        }

        if ($other instanceof ArrayKeyType) {
            return $other->isMatchedBy($this);
        }

        return $other instanceof self
            || $other instanceof NativeIntegerType
            || $other instanceof ScalarConcreteType
            || $other instanceof MixedType;
    }

    public function inferGenericsFrom(Type $other, Generics $generics): Generics
    {
        return $generics;
    }

    public function errorMessage(): ErrorMessage
    {
        return MessageBuilder::newError('Value {source_value} is not a valid positive integer.')
            ->withCode('invalid_positive_integer')
            ->build();
    }

    public function nativeType(): NativeIntegerType
    {
        return NativeIntegerType::get();
    }

    public function toString(): string
    {
        return 'positive-int';
    }
}
