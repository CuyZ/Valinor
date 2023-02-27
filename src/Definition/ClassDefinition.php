<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition;

use CuyZ\Valinor\Type\ClassType;

/** @internal */
final class ClassDefinition
{
    public function __construct(
        private ClassType $type,
        private Attributes $attributes,
        private Properties $properties,
        private Methods $methods,
        private bool $isFinal,
        private bool $isAbstract,
    ) {
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

    public function properties(): Properties
    {
        return $this->properties;
    }

    public function methods(): Methods
    {
        return $this->methods;
    }

    public function isFinal(): bool
    {
        return $this->isFinal;
    }

    public function isAbstract(): bool
    {
        return $this->isAbstract;
    }
}
