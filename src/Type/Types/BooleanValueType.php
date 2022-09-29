<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

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

    private bool $value;

    /**
     * @codeCoverageIgnore
     */
    private function __construct(bool $value)
    {
        $this->value = $value;
    }

    public static function true(): self
    {
        return self::$true ??= new self(true);
    }

    public static function false(): self
    {
        return self::$false ??= new self(false);
    }

    public function accepts($value): bool
    {
        return $value === $this->value;
    }

    public function matches(Type $other): bool
    {
        if ($other instanceof UnionType) {
            return $other->isMatchedBy($this);
        }

        return $other === $this
            || $other instanceof MixedType
            || $other instanceof NativeBooleanType;
    }

    public function canCast($value): bool
    {
        if ($value === $this->value) {
            return true;
        }

        if ($this->value === true) {
            return $value === '1' || $value === 1 || $value === 'true';
        }

        return $value === '0' || $value === 0 || $value === 'false';
    }

    public function cast($value): bool
    {
        assert($this->canCast($value));

        return $this->value;
    }

    public function errorMessage(): ErrorMessage
    {
        return MessageBuilder::newError('Value {source_value} does not match boolean value {expected_value}.')
            ->withParameter('expected_value', $this->toString())
            ->build();
    }

    public function value(): bool
    {
        return $this->value;
    }

    public function toString(): string
    {
        return $this->value ? 'true' : 'false';
    }
}
