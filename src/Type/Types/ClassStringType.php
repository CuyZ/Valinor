<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Type\ObjectType;
use CuyZ\Valinor\Type\StringType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\Exception\CannotCastValue;
use CuyZ\Valinor\Type\Types\Exception\InvalidClassString;
use Stringable;

use function class_exists;
use function interface_exists;
use function is_string;

/** @api */
final class ClassStringType implements StringType
{
    private ?ObjectType $subType;

    private string $signature;

    public function __construct(ObjectType $subType = null)
    {
        $this->subType = $subType;
        $this->signature = $this->subType
            ? "class-string<$this->subType>"
            : 'class-string';
    }

    public function accepts($value): bool
    {
        if (! is_string($value)) {
            return false;
        }

        if (! class_exists($value) && ! interface_exists($value)) {
            return false;
        }

        if (! $this->subType) {
            return true;
        }

        return is_a($value, $this->subType->className(), true);
    }

    public function matches(Type $other): bool
    {
        if ($other instanceof NativeStringType
            || $other instanceof NonEmptyStringType
            || $other instanceof MixedType
        ) {
            return true;
        }

        if ($other instanceof UnionType) {
            return $other->isMatchedBy($this);
        }

        if (! $other instanceof self) {
            return false;
        }

        if (! $this->subType) {
            return true;
        }

        if (! $other->subType) {
            return false;
        }

        return $this->subType->matches($other->subType);
    }

    public function canCast($value): bool
    {
        return is_string($value)
            || $value instanceof Stringable;
    }

    public function cast($value): string
    {
        if (! $this->canCast($value)) {
            throw new CannotCastValue($value, $this);
        }

        $value = (string)$value; // @phpstan-ignore-line

        if (! $this->subType) {
            return $value;
        }

        if (is_a($value, $this->subType->className(), true)) {
            return $value;
        }

        throw new InvalidClassString($value, $this->subType);
    }

    public function subType(): ?ObjectType
    {
        return $this->subType;
    }

    public function __toString(): string
    {
        return $this->signature;
    }
}
