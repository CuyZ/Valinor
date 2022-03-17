<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition;

use CuyZ\Valinor\Type\Type;

/** @api */
final class FunctionDefinition
{
    private string $name;

    private string $signature;

    private Parameters $parameters;

    private Type $returnType;

    /** @var class-string|null */
    private ?string $class;

    /**
     * @param class-string|null $class
     */
    public function __construct(
        string $name,
        string $signature,
        ?string $class,
        Parameters $parameters,
        Type $returnType
    ) {
        $this->name = $name;
        $this->signature = $signature;
        $this->class = $class;
        $this->parameters = $parameters;
        $this->returnType = $returnType;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function signature(): string
    {
        return $this->signature;
    }

    /**
     * @return class-string|null
     */
    public function class(): ?string
    {
        return $this->class;
    }

    /**
     * @phpstan-return Parameters
     * @return Parameters&ParameterDefinition[]
     */
    public function parameters(): Parameters
    {
        return $this->parameters;
    }

    public function returnType(): Type
    {
        return $this->returnType;
    }
}
