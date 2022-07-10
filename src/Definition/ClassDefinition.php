<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition;

use CuyZ\Valinor\Type\Types\ClassType;

/** @internal */
final class ClassDefinition
{
    private ClassType $type;

    private Attributes $attributes;

    private Properties $properties;

    private Methods $methods;

    public function __construct(
        ClassType $type,
        Attributes $attributes,
        Properties $properties,
        Methods $methods
    ) {
        $this->type = $type;
        $this->attributes = $attributes;
        $this->properties = $properties;
        $this->methods = $methods;
    }

    /**
     * @return class-string
     */
    public function name(): string
    {
        return $this->type->className();
    }

    public function type(): ClassType
    {
        return $this->type;
    }

    public function attributes(): Attributes
    {
        return $this->attributes;
    }

    /**
     * @phpstan-return Properties
     * @return Properties&PropertyDefinition[]
     */
    public function properties(): Properties
    {
        return $this->properties;
    }

    /**
     * @phpstan-return Methods
     * @return Methods&MethodDefinition[]
     */
    public function methods(): Methods
    {
        return $this->methods;
    }
}
