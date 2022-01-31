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

    public function __construct(string $name, string $signature, Parameters $parameters, Type $returnType)
    {
        $this->name = $name;
        $this->signature = $signature;
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
