<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Type\CompositeTraversableType;
use CuyZ\Valinor\Type\CompositeType;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\ShapedArrayElementDuplicatedKey;
use CuyZ\Valinor\Type\Type;

use function array_key_exists;
use function array_map;
use function assert;
use function count;
use function implode;
use function in_array;
use function is_array;

/** @internal */
final class ShapedArrayType implements CompositeType
{
    /** @var ShapedArrayElement[] */
    private array $elements;

    private bool $isUnsealed = false;

    private ?ArrayType $unsealedType = null;

    public function __construct(ShapedArrayElement ...$elements)
    {
        $this->elements = $elements;

        $keys = [];

        foreach ($elements as $element) {
            $key = $element->key()->value();

            if (in_array($key, $keys, true)) {
                throw new ShapedArrayElementDuplicatedKey((string)$key, $this->toString());
            }

            $keys[] = $key;
        }
    }

    public static function unsealed(ArrayType $unsealedType, ShapedArrayElement ...$elements): self
    {
        $self = new self(...$elements);
        $self->isUnsealed = true;
        $self->unsealedType = $unsealedType;

        return $self;
    }

    public static function unsealedWithoutType(ShapedArrayElement ...$elements): self
    {
        $self = new self(...$elements);
        $self->isUnsealed = true;

        return $self;
    }

    public function isUnsealed(): bool
    {
        return $this->isUnsealed;
    }

    public function hasUnsealedType(): bool
    {
        return $this->unsealedType !== null;
    }

    public function unsealedType(): ArrayType
    {
        assert($this->isUnsealed);

        return $this->unsealedType ?? ArrayType::native();
    }

    public function accepts(mixed $value): bool
    {
        if (! is_array($value)) {
            return false;
        }

        foreach ($this->elements as $shape) {
            $type = $shape->type();
            $key = $shape->key()->value();
            $valueExists = array_key_exists($key, $value);

            if (! $valueExists && ! $shape->isOptional()) {
                return false;
            }

            if ($valueExists && ! $type->accepts($value[$key])) {
                return false;
            }

            unset($value[$key]);
        }

        if ($this->isUnsealed) {
            return $this->unsealedType()->accepts($value);
        }

        return count($value) === 0;
    }

    public function matches(Type $other): bool
    {
        if ($other instanceof MixedType) {
            return true;
        }

        if ($other instanceof UnionType) {
            return $other->isMatchedBy($this);
        }

        if ($other instanceof CompositeTraversableType) {
            $keyType = $other->keyType();
            $subType = $other->subType();

            foreach ($this->elements as $element) {
                if (! $element->key()->matches($keyType)) {
                    return false;
                }

                if (! $element->type()->matches($subType)) {
                    return false;
                }
            }

            if ($this->isUnsealed && ! $this->unsealedType()->matches($other)) {
                return false;
            }

            return true;
        }

        if (! $other instanceof self) {
            return false;
        }

        foreach ($this->elements as $element) {
            foreach ($other->elements as $otherElement) {
                if ($element->key()->matches($otherElement->key())
                    && $element->type()->matches($otherElement->type())
                ) {
                    continue 2;
                }
            }

            if (! $element->isOptional()) {
                return false;
            }
        }

        if ($other->isUnsealed) {
            return $this->isUnsealed
                && $this->unsealedType()->matches($other->unsealedType());
        }

        return true;
    }

    public function traverse(): array
    {
        $types = [];

        foreach ($this->elements as $element) {
            $types[] = $type = $element->type();

            if ($type instanceof CompositeType) {
                $types = [...$types, ...$type->traverse()];
            }
        }

        if ($this->isUnsealed) {
            $types = [...$types, $this->unsealedType(), ...$this->unsealedType()->traverse()];
        }

        return $types;
    }

    /**
     * @return ShapedArrayElement[]
     */
    public function elements(): array
    {
        return $this->elements;
    }

    public function toString(): string
    {
        $signature = 'array{';
        $signature .= implode(', ', array_map(fn (ShapedArrayElement $element) => $element->toString(), $this->elements));

        if ($this->isUnsealed) {
            $signature .= ', ...';

            if ($this->unsealedType) {
                $signature .= $this->unsealedType->toString();
            }
        }

        $signature .= '}';

        return $signature;
    }
}
