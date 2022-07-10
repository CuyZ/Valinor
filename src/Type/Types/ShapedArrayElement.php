<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Type\FixedType;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\InvalidShapeElementType;
use CuyZ\Valinor\Type\Type;

/**
 * @PHP8.0 implement Stringable
 *
 * @internal
 */
final class ShapedArrayElement
{
    /** @var StringValueType|IntegerValueType */
    private FixedType $key;

    private Type $type;

    private bool $optional;

    /**
     * @param StringValueType|IntegerValueType $key
     */
    public function __construct(FixedType $key, Type $type, bool $optional = false)
    {
        $this->key = $key;
        $this->type = $type;
        $this->optional = $optional;

        if ($type instanceof FixedType) {
            throw new InvalidShapeElementType($this);
        }
    }

    /**
     * @return StringValueType|IntegerValueType
     */
    public function key(): FixedType
    {
        return $this->key;
    }

    public function type(): Type
    {
        return $this->type;
    }

    public function isOptional(): bool
    {
        return $this->optional;
    }

    public function __toString(): string
    {
        return $this->isOptional()
            ? "$this->key?: $this->type"
            : "$this->key: $this->type";
    }
}
