<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object;

use CuyZ\Valinor\Definition\Attributes;
use CuyZ\Valinor\Definition\ParameterDefinition;
use CuyZ\Valinor\Definition\PropertyDefinition;
use CuyZ\Valinor\Type\Type;

/** @internal */
final class Argument
{
    /** @var non-empty-string */
    private string $name;

    private Type $type;

    private mixed $defaultValue = null;

    private bool $isRequired = true;

    private Attributes $attributes;

    /**
     * @param non-empty-string $name
     */
    public function __construct(string $name, Type $type)
    {
        $this->type = $type;
        $this->name = $name;
    }

    public static function fromParameter(ParameterDefinition $parameter): self
    {
        $instance = new self($parameter->name, $parameter->type);
        $instance->attributes = $parameter->attributes;

        if ($parameter->isOptional) {
            $instance->defaultValue = $parameter->defaultValue;
            $instance->isRequired = false;
        }

        return $instance;
    }

    public static function fromProperty(PropertyDefinition $property): self
    {
        $instance = new self($property->name, $property->type);
        $instance->attributes = $property->attributes;

        if ($property->hasDefaultValue) {
            $instance->defaultValue = $property->defaultValue;
            $instance->isRequired = false;
        }

        return $instance;
    }

    /**
     * @return non-empty-string
     */
    public function name(): string
    {
        return $this->name;
    }

    public function type(): Type
    {
        return $this->type;
    }

    public function defaultValue(): mixed
    {
        return $this->defaultValue;
    }

    public function isRequired(): bool
    {
        return $this->isRequired;
    }

    public function attributes(): Attributes
    {
        return $this->attributes ??= Attributes::empty();
    }
}
