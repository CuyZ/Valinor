<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition;

use CuyZ\Valinor\Type\Type;

/** @api */
final class ParameterDefinition
{
    private string $name;

    private string $signature;

    private Type $type;

    private bool $isOptional;

    private bool $isVariadic;

    /** @var mixed */
    private $defaultValue;

    private Attributes $attributes;

    /**
     * @param mixed $defaultValue
     */
    public function __construct(
        string $name,
        string $signature,
        Type $type,
        bool $isOptional,
        bool $isVariadic,
        $defaultValue,
        Attributes $attributes
    ) {
        $this->name = $name;
        $this->signature = $signature;
        $this->type = $type;
        $this->isOptional = $isOptional;
        $this->isVariadic = $isVariadic;
        $this->defaultValue = $defaultValue;
        $this->attributes = $attributes;
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

    /**
     * @return mixed
     */
    public function defaultValue()
    {
        return $this->defaultValue;
    }

    public function attributes(): Attributes
    {
        return $this->attributes;
    }
}
