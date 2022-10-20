<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition;

use CuyZ\Valinor\Type\Type;

/** @internal */
final class ParameterDefinition
{
    public function __construct(
        private string $name,
        private string $signature,
        private Type $type,
        private bool $isOptional,
        private bool $isVariadic,
        private mixed $defaultValue,
        private Attributes $attributes
    ) {
    }

    public function name(): string
    {
        return $this->name;
    }

    public function signature(): string
    {
        return $this->signature;
    }

    public function type(): Type
    {
        return $this->type;
    }

    public function isOptional(): bool
    {
        return $this->isOptional;
    }

    public function isVariadic(): bool
    {
        return $this->isVariadic;
    }

    public function defaultValue(): mixed
    {
        return $this->defaultValue;
    }

    public function attributes(): Attributes
    {
        return $this->attributes;
    }
}
