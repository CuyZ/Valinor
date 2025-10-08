<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Compiler\Native\ComplianceNode;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use CuyZ\Valinor\Mapper\Tree\Message\MessageBuilder;
use CuyZ\Valinor\Type\BooleanType;
use CuyZ\Valinor\Type\FixedType;
use CuyZ\Valinor\Type\Type;

use function assert;

/** @internal */
final class BooleanValueType implements BooleanType, FixedType
{
    private static self $true;

    private static self $false;

    private function __construct(private bool $value) {}

    public static function true(): self
    {
        return self::$true ??= new self(true);
    }

    public static function false(): self
    {
        return self::$false ??= new self(false);
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
        if ($value === $this->value) {
            return true;
        }

        if ($this->value === true) {
            return $value === '1' || $value === 1 || $value === 'true';
        }

        return $value === '0' || $value === 0 || $value === 'false';
    }

    public function cast(mixed $value): bool
    {
        assert($this->canCast($value));

        return $this->value;
    }

    public function errorMessage(): ErrorMessage
    {
        return MessageBuilder::newError('Value {source_value} does not match boolean value {expected_value}.')
            ->withCode('invalid_boolean_value')
            ->withParameter('expected_value', $this->toString())
            ->build();
    }

    public function value(): bool
    {
        return $this->value;
    }

    public function nativeType(): NativeBooleanType
    {
        return NativeBooleanType::get();
    }

    public function toString(): string
    {
        return $this->value ? 'true' : 'false';
    }
}
