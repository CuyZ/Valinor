<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use CuyZ\Valinor\Mapper\Tree\Message\MessageBuilder;
use CuyZ\Valinor\Type\FixedType;
use CuyZ\Valinor\Type\StringType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Utility\ValueDumper;
use Stringable;

use function is_numeric;
use function is_string;

/** @internal */
final class StringValueType implements StringType, FixedType
{
    private string $quoteChar;

    public function __construct(private string $value)
    {
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

    public function accepts(mixed $value): bool
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
            ->withParameter('expected_value', ValueDumper::dump($this->value))
            ->build();
    }

    public function toString(): string
    {
        if (isset($this->quoteChar)) {
            return $this->quoteChar . $this->value . $this->quoteChar;
        }

        return $this->value;
    }
}
