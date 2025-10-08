<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Compiler\Native\ComplianceNode;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use CuyZ\Valinor\Mapper\Tree\Message\MessageBuilder;
use CuyZ\Valinor\Type\StringType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Utility\IsSingleton;
use Stringable;

use function assert;
use function is_numeric;
use function is_string;

/** @internal */
final class NonEmptyStringType implements StringType
{
    use IsSingleton;

    public function accepts(mixed $value): bool
    {
        return is_string($value) && $value !== '';
    }

    public function compiledAccept(ComplianceNode $node): ComplianceNode
    {
        return Node::functionCall('is_string', [$node])->and($node->different(Node::value('')));
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
            || $other instanceof NativeStringType
            || $other instanceof ScalarConcreteType
            || $other instanceof MixedType;
    }

    public function inferGenericsFrom(Type $other, Generics $generics): Generics
    {
        return $generics;
    }

    public function canCast(mixed $value): bool
    {
        return (is_string($value) || is_numeric($value) || $value instanceof Stringable)
            && (string)$value !== '';
    }

    public function cast(mixed $value): string
    {
        assert($this->canCast($value));

        return (string)$value; // @phpstan-ignore-line
    }

    public function errorMessage(): ErrorMessage
    {
        return MessageBuilder::newError('Value {source_value} is not a valid non-empty string.')
            ->withCode('invalid_non_empty_string')
            ->build();
    }

    public function nativeType(): NativeStringType
    {
        return NativeStringType::get();
    }

    public function toString(): string
    {
        return 'non-empty-string';
    }
}
