<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Type\CompositeType;
use CuyZ\Valinor\Type\ObjectType;
use CuyZ\Valinor\Type\StringType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\Exception\CannotCastValue;
use CuyZ\Valinor\Type\Types\Exception\InvalidClassString;
use CuyZ\Valinor\Type\Types\Exception\InvalidUnionOfClassString;

use function class_exists;
use function interface_exists;
use function is_object;
use function is_string;
use function method_exists;

/** @api */
final class ClassStringType implements StringType, CompositeType
{
    /** @var ObjectType|UnionType|null */
    private ?Type $subType;

    private string $signature;

    /**
     * @param ObjectType|UnionType $subType
     */
    public function __construct(Type $subType = null)
    {
        if ($subType instanceof UnionType) {
            foreach ($subType->types() as $type) {
                if (! $type instanceof ObjectType) {
                    throw new InvalidUnionOfClassString($subType);
                }
            }
        }

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

        if ($this->subType instanceof ObjectType) {
            return is_a($value, $this->subType->className(), true);
        }

        foreach ($this->subType->types() as $type) {
            /** @var ObjectType $type */
            if (is_a($value, $type->className(), true)) {
                return true;
            }
        }

        return false;
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
            // @PHP8.0 `$value instanceof Stringable`
            || (is_object($value) && method_exists($value, '__toString'));
    }

    public function cast($value): string
    {
        if (! $this->canCast($value)) {
            throw new CannotCastValue($value, $this);
        }

        $value = (string)$value; // @phpstan-ignore-line

        if (! $this->accepts($value)) {
            throw new InvalidClassString($value, $this->subType);
        }

        return $value;
    }

    /**
     * @return ObjectType|UnionType|null
     */
    public function subType(): ?Type
    {
        return $this->subType;
    }

    public function traverse(): iterable
    {
        if (! $this->subType) {
            return [];
        }

        yield $this->subType;

        if ($this->subType instanceof CompositeType) {
            yield from $this->subType->traverse();
        }
    }

    public function __toString(): string
    {
        return $this->signature;
    }
}
