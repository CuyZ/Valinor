<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Compiler\Native\ComplianceNode;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use CuyZ\Valinor\Mapper\Tree\Message\MessageBuilder;
use CuyZ\Valinor\Type\FixedType;
use CuyZ\Valinor\Type\IntegerType;
use CuyZ\Valinor\Type\Type;

use function assert;
use function filter_var;
use function is_bool;
use function is_string;
use function ltrim;
use function preg_match;

/** @internal */
final class IntegerValueType implements IntegerType, FixedType
{
    public function __construct(private int $value) {}

    public function accepts(mixed $value): bool
    {
        return $value === $this->value;
    }

    public function compiledAccept(ComplianceNode $node): ComplianceNode
    {
        return $node->equals(Node::value($this->value));
    }

    public function matches(Type $other): bool
    {
        if ($other instanceof UnionType) {
            return $other->isMatchedBy($this);
        }

        if ($other instanceof self) {
            return $this->value === $other->value;
        }

        if ($other instanceof ArrayKeyType) {
            return $other->isMatchedBy($this);
        }

        if ($other instanceof NativeIntegerType || $other instanceof MixedType) {
            return true;
        }

        if ($other instanceof NegativeIntegerType && $this->value < 0) {
            return true;
        }

        if ($other instanceof PositiveIntegerType && $this->value > 0) {
            return true;
        }

        if ($other instanceof NonNegativeIntegerType && $this->value >= 0) {
            return true;
        }

        if ($other instanceof NonPositiveIntegerType && $this->value <= 0) {
            return true;
        }

        if ($other instanceof IntegerRangeType) {
            return $other->accepts($this->value);
        }

        return false;
    }

    public function canCast(mixed $value): bool
    {
        if (is_string($value)) {
            $value = preg_match('/^0+$/', $value)
                ? '0'
                : ltrim($value, '0');
        }

        return ! is_bool($value)
            && filter_var($value, FILTER_VALIDATE_INT) !== false
            && (int)$value === $this->value; // @phpstan-ignore-line;
    }

    public function cast(mixed $value): int
    {
        assert($this->canCast($value));

        return (int)$value; // @phpstan-ignore-line;
    }

    public function errorMessage(): ErrorMessage
    {
        return MessageBuilder::newError('Value {source_value} does not match integer value {expected_value}.')
            ->withParameter('expected_value', (string)$this->value)
            ->build();
    }

    public function value(): int
    {
        return $this->value;
    }

    /**
     * @return non-empty-string
     */
    public function toString(): string
    {
        return (string)$this->value;
    }
}
