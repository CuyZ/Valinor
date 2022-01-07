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
use function is_subclass_of;

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

        $subClass = $this->subType->signature()->className();

        /** @phpstan-ignore-next-line @see https://github.com/phpstan/phpstan-src/pull/397 */
        return $value === $subClass || is_subclass_of($value, $subClass);
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

        $subClass = $this->subType->signature()->className();

        if ($value === $subClass || is_subclass_of($value, $subClass)) {
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
