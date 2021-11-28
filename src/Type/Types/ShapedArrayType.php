<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Type\CompositeTraversableType;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\ShapedArrayElementDuplicatedKey;
use CuyZ\Valinor\Type\TraversableType;
use CuyZ\Valinor\Type\Type;

use function array_key_exists;
use function implode;
use function in_array;
use function is_array;

final class ShapedArrayType implements TraversableType
{
    /** @var ShapedArrayElement[] */
    private array $elements;

    private string $signature;

    public function __construct(ShapedArrayElement ...$elements)
    {
        $this->elements = $elements;
        $this->signature = 'array{' . implode(', ', $elements) . '}';

        $keys = [];

        foreach ($elements as $element) {
            $key = $element->key()->value();

            if (in_array($key, $keys, true)) {
                throw new ShapedArrayElementDuplicatedKey((string)$key, $this->signature);
            }

            $keys[] = $key;
        }
    }

    public function accepts($value): bool
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

        return true;
    }

    /**
     * @return ShapedArrayElement[]
     */
    public function elements(): array
    {
        return $this->elements;
    }

    public function __toString(): string
    {
        return $this->signature;
    }
}
