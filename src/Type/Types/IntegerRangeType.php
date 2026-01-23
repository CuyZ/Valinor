<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Compiler\Native\ComplianceNode;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use CuyZ\Valinor\Mapper\Tree\Message\MessageBuilder;
use CuyZ\Valinor\Type\IntegerType;
use CuyZ\Valinor\Type\Parser\Exception\Scalar\ReversedValuesForIntegerRange;
use CuyZ\Valinor\Type\Parser\Exception\Scalar\SameValueForIntegerRange;
use CuyZ\Valinor\Type\Type;

use function assert;
use function filter_var;
use function is_bool;
use function is_int;
use function is_string;
use function ltrim;
use function sprintf;

/** @internal */
final class IntegerRangeType implements IntegerType
{
    public function __construct(
        private int $min,
        private int $max
    ) {}

    public static function from(int $min, int $max): self
    {
        if ($min > $max) {
            throw new ReversedValuesForIntegerRange($min, $max);
        }

        if ($min === $max) {
            throw new SameValueForIntegerRange($min);
        }

        return new self($min, $max);
    }

    public function accepts(mixed $value): bool
    {
        return is_int($value)
            && $value >= $this->min
            && $value <= $this->max;
    }

    public function compiledAccept(ComplianceNode $node): ComplianceNode
    {
        return Node::functionCall('is_int', [$node])
            ->and($node->isGreaterOrEqualsTo(Node::value($this->min)))
            ->and($node->isLessOrEqualsTo(Node::value($this->max)));
    }

    public function matches(Type $other): bool
    {
        if ($other instanceof UnionType) {
            return $other->isMatchedBy($this);
        }

        if ($other instanceof NativeIntegerType || $other instanceof ScalarConcreteType || $other instanceof MixedType) {
            return true;
        }

        if ($other instanceof IntegerValueType && $this->accepts($other->value())) {
            return true;
        }

        if ($other instanceof NegativeIntegerType && $this->max < 0) {
            return true;
        }

        if ($other instanceof PositiveIntegerType && $this->min > 0) {
            return true;
        }

        if ($other instanceof ArrayKeyType) {
            return $other->isMatchedBy($this);
        }

        if ($other instanceof self) {
            return $other->min === $this->min && $other->max === $this->max;
        }

        return false;
    }

    public function inferGenericsFrom(Type $other, Generics $generics): Generics
    {
        return $generics;
    }

    public function canCast(mixed $value): bool
    {
        if (is_string($value) && $value !== '') {
            $value = ltrim($value, '0') ?: '0';
        }

        return ! is_bool($value)
            && filter_var($value, FILTER_VALIDATE_INT) !== false
            && $value >= $this->min
            && $value <= $this->max;
    }

    public function cast(mixed $value): int
    {
        assert($this->canCast($value));

        return (int)$value; // @phpstan-ignore-line
    }

    public function errorMessage(): ErrorMessage
    {
        return MessageBuilder::newError('Value {source_value} is not a valid integer between {min} and {max}.')
            ->withCode('invalid_integer_range')
            ->withParameter('min', (string)$this->min)
            ->withParameter('max', (string)$this->max)
            ->build();
    }

    public function min(): int
    {
        return $this->min;
    }

    public function max(): int
    {
        return $this->max;
    }

    public function nativeType(): NativeIntegerType
    {
        return NativeIntegerType::get();
    }

    public function toString(): string
    {
        return sprintf(
            'int<%s, %s>',
            $this->min > PHP_INT_MIN ? $this->min : 'min',
            $this->max < PHP_INT_MAX ? $this->max : 'max'
        );
    }
}
