<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Type\FixedType;
use CuyZ\Valinor\Type\StringType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\Exception\InvalidStringValue;
use CuyZ\Valinor\Type\Types\Exception\InvalidStringValueType;
use Stringable;

use function is_numeric;
use function is_string;

/** @api */
final class StringValueType implements StringType, FixedType
{
    private string $value;

    private string $quoteChar;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function singleQuote(string $value): self
    {
        $instance = new self($value);
        $instance->quoteChar = "'";

        return $instance;
    }

    public static function doubleQuote(string $value): self
    {
        $instance = new self($value);
        $instance->quoteChar = '"';

        return $instance;
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

        if ($other instanceof self) {
            return $this->value === $other->value;
        }

        if ($other instanceof ArrayKeyType) {
            return $other->isMatchedBy($this);
        }

        return $other instanceof StringType
            || $other instanceof MixedType;
    }

    public function canCast($value): bool
    {
        return is_string($value)
            || is_numeric($value)
            || $value instanceof Stringable;
    }

    public function cast($value): string
    {
        if (! $this->canCast($value)) {
            throw new InvalidStringValueType($value, $this->value);
        }

        $value = (string)$value; // @phpstan-ignore-line

        if ($value !== $this->value) {
            throw new InvalidStringValue($value, $this->value);
        }

        return $value;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        if (isset($this->quoteChar)) {
            return $this->quoteChar . $this->value . $this->quoteChar;
        }

        return $this->value;
    }
}
