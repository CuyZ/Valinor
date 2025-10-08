<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Compiler\Native\ComplianceNode;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use CuyZ\Valinor\Mapper\Tree\Message\MessageBuilder;
use CuyZ\Valinor\Type\BooleanType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Utility\IsSingleton;

use function assert;
use function is_bool;

/** @internal */
final class NativeBooleanType implements BooleanType
{
    use IsSingleton;

    public function accepts(mixed $value): bool
    {
        return is_bool($value);
    }

    public function compiledAccept(ComplianceNode $node): ComplianceNode
    {
        return Node::functionCall('is_bool', [$node]);
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

    public function canCast(mixed $value): bool
    {
        return is_bool($value)
            || $value === '1'
            || $value === '0'
            || $value === 1
            || $value === 0
            || $value === 'true'
            || $value === 'false';
    }

    public function cast(mixed $value): bool
    {
        assert($this->canCast($value));

        if ($value === 'false') {
            return false;
        }

        return (bool)$value;
    }

    public function errorMessage(): ErrorMessage
    {
        return MessageBuilder::newError('Value {source_value} is not a valid boolean.')
            ->withCode('invalid_boolean')
            ->build();
    }

    public function nativeType(): NativeBooleanType
    {
        return $this;
    }

    public function toString(): string
    {
        return 'bool';
    }
}
