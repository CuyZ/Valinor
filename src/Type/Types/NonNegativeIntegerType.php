<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Compiler\Native\ComplianceNode;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use CuyZ\Valinor\Mapper\Tree\Message\MessageBuilder;
use CuyZ\Valinor\Type\IntegerType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Utility\IsSingleton;

use function is_string;
use function ltrim;

/** @internal */
final class NonNegativeIntegerType implements IntegerType
{
    use IsSingleton;

    public function accepts(mixed $value): bool
    {
        return is_int($value) && $value >= 0;
    }

    public function compiledAccept(ComplianceNode $node): ComplianceNode
    {
        return Node::functionCall('is_int', [$node])->and($node->isGreaterOrEqualsTo(Node::value(0)));
    }

    public function matches(Type $other): bool
    {
        if ($other instanceof UnionType) {
            return $other->isMatchedBy($this);
        }

        return $other instanceof self
            || $other instanceof NativeIntegerType
            || $other instanceof ScalarConcreteType
            || $other instanceof MixedType;
    }

    public function canCast(mixed $value): bool
    {
        if (is_string($value)) {
            $value = ltrim($value, '0') . '0';
        }

        return ! is_bool($value)
            && filter_var($value, FILTER_VALIDATE_INT) !== false
            && $value >= 0;
    }

    public function cast(mixed $value): int
    {
        assert($this->canCast($value));

        return (int)$value; // @phpstan-ignore-line
    }

    public function errorMessage(): ErrorMessage
    {
        return MessageBuilder::newError('Value {source_value} is not a valid non-negative integer.')
            ->withCode('invalid_non_negative_integer')
            ->build();
    }

    public function nativeType(): NativeIntegerType
    {
        return NativeIntegerType::get();
    }

    public function toString(): string
    {
        return 'non-negative-int';
    }
}
