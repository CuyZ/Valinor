<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Type\CompositeTraversableType;
use CuyZ\Valinor\Type\CompositeType;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\ShapedArrayElementDuplicatedKey;
use CuyZ\Valinor\Type\Type;

use function array_diff;
use function array_key_exists;
use function array_keys;
use function array_map;
use function count;
use function implode;
use function in_array;
use function is_array;

/** @internal */
final class ShapedArrayType implements CompositeType
{
    /** @var ShapedArrayElement[] */
    private array $elements;

    private string $signature;

    public function __construct(
        public readonly ArrayKeyType|null $extra_key,
        public readonly Type|null $extra_type,
        ShapedArrayElement ...$elements
    ) {
        if ($extra_type !== null) {
            $extra_key ??= ArrayKeyType::default();
        }
        assert(is_null($extra_key) === is_null($extra_type));

        $this->elements = $elements;
        $this->signature =
            'array{'
            . implode(', ', array_map(fn (ShapedArrayElement $element) => $element->toString(), $elements))
            . (
                $extra_key === null || $extra_type === null
                ? ''
                : ', ...<'.$extra_key->toString().', '.$extra_type->toString().'>'
            )
            . '}';

        $keys = [];

        foreach ($elements as $element) {
            $key = $element->key()->value();

            if (in_array($key, $keys, true)) {
                throw new ShapedArrayElementDuplicatedKey((string)$key, $this->signature);
            }

            $keys[] = $key;
        }
    }

    public function accepts(mixed $value): bool
    {
        if (! is_array($value)) {
            return false;
        }

        $keys = [];

        foreach ($this->elements as $shape) {
            $type = $shape->type();
            $keys[] = $key = $shape->key()->value();
            $valueExists = array_key_exists($key, $value);

            if (! $valueExists && ! $shape->isOptional()) {
                return false;
            }

            if ($valueExists && ! $type->accepts($value[$key])) {
                return false;
            }
        }

        $excess = array_diff(array_keys($value), $keys);

        if ($this->extra_key === null) {
            return count($excess) === 0;
        }

        assert($this->extra_type !== null);
        foreach ($excess as $key) {
            if (!$this->extra_key->accepts($key)) {
                return false;
            }
            if (!$this->extra_type->accepts($value[$key])) {
                return false;
            }
        }

        return true;
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

            return true;
        }

        if (! $other instanceof self) {
            return false;
        }

        foreach ($this->elements as $element) {
            foreach ($other->elements as $otherElement) {
                if ($element->key()->matches($otherElement->key())) {
                    if (! $element->type()->matches($otherElement->type())) {
                        return false;
                    }

                    continue 2;
                }
            }

            if (! $element->isOptional()) {
                return false;
            }
        }

        if ($other->extra_key === null && $this->extra_key === null) {
            return true;
        }
        if (is_null($other->extra_key) !== is_null($this->extra_key)) {
            return false;
        }
        assert($this->extra_key !== null);
        assert($this->extra_type !== null);
        assert($other->extra_key !== null);
        assert($other->extra_type !== null);

        if (!$other->extra_key->matches($this->extra_key)) {
            return false;
        }
        if (!$other->extra_type->matches($this->extra_type)) {
            return false;
        }
        return true;
    }

    public function traverse(): array
    {
        $types = [];

        foreach ([...$this->elements, $this->extra_key, $this->extra_type] as $element) {
            if ($element === null) {
                continue;
            }
            if ($element instanceof ShapedArrayElement) {
                $element = $element->type();
            }
            $types[] = $type = $element;

            if ($type instanceof CompositeType) {
                $types = [...$types, ...$type->traverse()];
            }
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
        return $this->signature;
    }
}
