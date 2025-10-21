<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Compiler\Native\ComplianceNode;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use CuyZ\Valinor\Mapper\Tree\Message\MessageBuilder;
use CuyZ\Valinor\Type\FixedType;
use CuyZ\Valinor\Type\FloatType;
use CuyZ\Valinor\Type\Type;

use function assert;
use function is_numeric;

/** @internal */
final class FloatValueType implements FloatType, FixedType
{
    public function __construct(private float $value) {}

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
        return $other->accepts($this->value);
    }

    public function inferGenericsFrom(Type $other, Generics $generics): Generics
    {
        return $generics;
    }

    public function canCast(mixed $value): bool
    {
        return is_numeric($value) && (float)$value === $this->value;
    }

    public function cast(mixed $value): float
    {
        assert($this->canCast($value));

        return (float)$value; // @phpstan-ignore-line
    }

    public function errorMessage(): ErrorMessage
    {
        return MessageBuilder::newError('Value {source_value} does not match float value {expected_value}.')
            ->withCode('invalid_float_value')
            ->withParameter('expected_value', (string)$this->value)
            ->build();
    }

    public function value(): float
    {
        return $this->value;
    }

    public function nativeType(): NativeFloatType
    {
        return NativeFloatType::get();
    }

    public function toString(): string
    {
        return (string)$this->value;
    }
}
