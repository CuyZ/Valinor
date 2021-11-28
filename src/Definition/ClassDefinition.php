<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition;

final class ClassDefinition
{
    /** @var class-string */
    private string $className;

    private string $signature;

    private Attributes $attributes;

    private Properties $properties;

    private Methods $methods;

    /**
     * @param class-string $className
     */
    public function __construct(
        string $className,
        string $signature,
        Attributes $attributes,
        Properties $properties,
        Methods $methods
    ) {
        $this->className = $className;
        $this->signature = $signature;
        $this->attributes = $attributes;
        $this->properties = $properties;
        $this->methods = $methods;
    }

    /**
     * @return class-string
     */
    public function name(): string
    {
        return $this->className;
    }

    public function signature(): string
    {
        return $this->signature;
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
