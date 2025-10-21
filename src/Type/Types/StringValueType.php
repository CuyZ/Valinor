<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Compiler\Native\ComplianceNode;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use CuyZ\Valinor\Mapper\Tree\Message\MessageBuilder;
use CuyZ\Valinor\Type\FixedType;
use CuyZ\Valinor\Type\StringType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Utility\ValueDumper;
use Stringable;

use function assert;
use function is_numeric;
use function is_string;
use function str_starts_with;
use function substr;

/** @internal */
final class StringValueType implements StringType, FixedType
{
    private string $quoteChar;

    public function __construct(private string $value) {}

    public static function from(string $value): self
    {
        if (! str_starts_with($value, '"') && ! str_starts_with($value, "'")) {
            return new self($value);
        }

        $instance = new self(substr($value, 1, -1));
        $instance->quoteChar = $value[0];

        return $instance;
    }

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
        return (is_string($value) || is_numeric($value) || $value instanceof Stringable)
            && (string)$value === $this->value;
    }

    public function cast(mixed $value): string
    {
        assert($this->canCast($value));

        return $this->value;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function errorMessage(): ErrorMessage
    {
        return MessageBuilder::newError('Value {source_value} does not match string value {expected_value}.')
            ->withCode('invalid_string_value')
            ->withParameter('expected_value', ValueDumper::dump($this->value))
            ->build();
    }

    public function nativeType(): NativeStringType
    {
        return NativeStringType::get();
    }

    public function toString(): string
    {
        if (isset($this->quoteChar)) {
            return $this->quoteChar . $this->value . $this->quoteChar;
        }

        return $this->value;
    }
}
