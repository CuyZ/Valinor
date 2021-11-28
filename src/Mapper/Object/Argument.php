<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object;

use CuyZ\Valinor\Definition\Attributes;
use CuyZ\Valinor\Definition\EmptyAttributes;
use CuyZ\Valinor\Type\Type;

final class Argument
{
    private string $name;

    private Type $type;

    /** @var mixed */
    private $value;

    private ?Attributes $attributes;

    /**
     * @param mixed $value
     */
    public function __construct(string $name, Type $type, $value, Attributes $attributes = null)
    {
        $this->name = $name;
        $this->type = $type;
        $this->value = $value;
        $this->attributes = $attributes;
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
    public function value()
    {
        return $this->value;
    }

    public function attributes(): Attributes
    {
        return $this->attributes ?? EmptyAttributes::get();
    }
}
