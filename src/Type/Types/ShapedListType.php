<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Type\CompositeTraversableType;
use CuyZ\Valinor\Type\CompositeType;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\ShapedListNonMonotonicKey;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\ShapedListRequiredValueAfterOptional;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\ShapedListStringKey;
use CuyZ\Valinor\Type\Type;

use function array_key_exists;
use function array_map;
use function implode;
use function is_array;

/** @internal */
final class ShapedListType implements CompositeType
{
    /** @var ShapedArrayElement[] */
    private array $elements;

    private string $signature;

    public function __construct(
        public readonly ?Type $extra,
        ShapedArrayElement ...$elements
    ) {
        $this->signature =
            'list{'
            . implode(', ', array_map(fn (ShapedArrayElement $element) => $element->toString(), $elements))
            . ($extra ? ', ...<'.$extra->toString().'>' : '')
            . '}';

        $ordered = [];
        $k = 0;
        $optional = false;
        foreach ($elements as $element) {
            $key = $element->key()->value();

            $optional = $element->isOptional() || $optional;

            if ($optional !== $element->isOptional()) {
                throw new ShapedListRequiredValueAfterOptional((string) $key, $this->signature);
            }

            if (is_string($key)) {
                throw new ShapedListStringKey($key, $this->signature);
            }

            if ($key !== $k++) {
                throw new ShapedListNonMonotonicKey($key, $k, $this->signature);
            }

            $ordered[] = $element;
        }

        $this->elements = $elements;
    }

    public function accepts(mixed $value): bool
    {
        if (! is_array($value)) {
            return false;
        }

        $i = 0;

        foreach ($value as $key => $item) {
            if ($key !== $i++) {
                return false;
            }

            if (!array_key_exists($key, $this->elements)) {
                if ($this->extra === null) {
                    return false;
                }
                if (!$this->extra->accepts($item)) {
                    return false;
                }
                continue;
            }

            $type = $this->elements[$key]->type();
            if (! $type->accepts($item)) {
                return false;
            }
        }

        if (array_key_exists($i, $this->elements)
            && !$this->elements[$i]->isOptional()
        ) {
            return false;
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

        if ($other instanceof ListType) {
            $subType = $other->subType();

            foreach ($this->elements as $element) {
                if (! $element->type()->matches($subType)) {
                    return false;
                }
            }

            return true;
        }

        if ($other instanceof CompositeTraversableType) {
            if ($other->keyType()->accepts(ArrayKeyType::string())) {
                return false;
            }
            $subType = $other->subType();

            foreach ($this->elements as $element) {
                if (! $element->type()->matches($subType)) {
                    return false;
                }
            }

            return true;
        }

        if (! $other instanceof self) {
            return false;
        }

        foreach ($this->elements as $k => $element) {
            if (!array_key_exists($k, $other->elements)) {
                return false;
            }
            $otherElement = $other->elements[$k];
            if (!$element->type()->matches($otherElement->type())) {
                return false;
            }
            if ($element->isOptional() !== $otherElement->isOptional()) {
                return false;
            }
        }

        if ($other->extra === null && $this->extra === null) {
            return true;
        }
        if (is_null($other->extra) !== is_null($this->extra)) {
            return false;
        }
        assert($other->extra !== null);
        assert($this->extra !== null);

        return $other->extra->matches($this->extra);
    }

    public function traverse(): array
    {
        $types = [];

        foreach ([...$this->elements, $this->extra] as $element) {
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
