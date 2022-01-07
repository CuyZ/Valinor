<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition;

use CuyZ\Valinor\Type\Type;

/** @api */
final class PropertyDefinition
{
    private string $name;

    private string $signature;

    private Type $type;

    private bool $hasDefaultValue;

    /** @var mixed */
    private $defaultValue;

    private bool $isPublic;

    private Attributes $attributes;

    /**
     * @param mixed $defaultValue
     */
    public function __construct(
        string $name,
        string $signature,
        Type $type,
        bool $hasDefaultValue,
        $defaultValue,
        bool $isPublic,
        Attributes $attributes
    ) {
        $this->name = $name;
        $this->signature = $signature;
        $this->type = $type;
        $this->hasDefaultValue = $hasDefaultValue;
        $this->defaultValue = $defaultValue;
        $this->isPublic = $isPublic;
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

    public function hasDefaultValue(): bool
    {
        return $this->hasDefaultValue;
    }

    /**
     * @return mixed
     */
    public function defaultValue()
    {
        return $this->defaultValue;
    }

    public function isPublic(): bool
    {
        return $this->isPublic;
    }

    public function attributes(): Attributes
    {
        return $this->attributes;
    }
}
