<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Type\Type;

/** @internal */
final class ShapedArrayElement
{
    public function __construct(
        private StringValueType|IntegerValueType $key,
        private Type $type,
        private bool $optional = false
    ) {
    }

    public function key(): StringValueType|IntegerValueType
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

    public function toString(): string
    {
        return $this->isOptional()
            ? "{$this->key->toString()}?: {$this->type->toString()}"
            : "{$this->key->toString()}: {$this->type->toString()}";
    }
}
