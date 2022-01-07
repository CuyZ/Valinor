<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object;

use CuyZ\Valinor\Definition\Attributes;
use CuyZ\Valinor\Definition\EmptyAttributes;
use CuyZ\Valinor\Type\Type;

/** @api */
final class Argument
{
    private string $name;

    private Type $type;

    /** @var mixed */
    private $defaultValue;

    private bool $isRequired = true;

    private Attributes $attributes;

    private function __construct(string $name, Type $type)
    {
        $this->name = $name;
        $this->type = $type;
    }

    public static function required(string $name, Type $type): self
    {
        return new self($name, $type);
    }

    /**
     * @param mixed $defaultValue
     */
    public static function optional(string $name, Type $type, $defaultValue): self
    {
        $instance = new self($name, $type);
        $instance->defaultValue = $defaultValue;
        $instance->isRequired = false;

        return $instance;
    }

    public function withAttributes(Attributes $attributes): self
    {
        $clone = clone $this;
        $clone->attributes = $attributes;

        return $clone;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function type(): Type
    {
        return $this->type;
    }

    /**
     * @return mixed
     */
    public function defaultValue()
    {
        return $this->defaultValue;
    }

    public function isRequired(): bool
    {
        return $this->isRequired;
    }

    public function attributes(): Attributes
    {
        return $this->attributes ?? EmptyAttributes::get();
    }
}
