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

    public function __construct(ShapedArrayElement ...$elements)
    {
        $this->elements = $elements;
        $this->signature =
            'array{' .
            implode(', ', array_map(fn (ShapedArrayElement $element) => $element->toString(), $elements))
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

        return count($excess) === 0;
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
