<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition;

use CuyZ\Valinor\Type\Type;

/** @internal */
final class FunctionDefinition
{
    private string $name;

    private string $signature;

    private ?string $fileName;

    /** @var class-string|null */
    private ?string $class;

    private bool $isStatic;

    private Parameters $parameters;

    private Type $returnType;

    /**
     * @param class-string|null $class
     */
    public function __construct(
        string $name,
        string $signature,
        ?string $fileName,
        ?string $class,
        bool $isStatic,
        Parameters $parameters,
        Type $returnType
    ) {
        $this->name = $name;
        $this->signature = $signature;
        $this->fileName = $fileName;
        $this->class = $class;
        $this->isStatic = $isStatic;
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

    public function fileName(): ?string
    {
        return $this->fileName;
    }

    /**
     * @return class-string|null
     */
    public function class(): ?string
    {
        return $this->class;
    }

    public function isStatic(): bool
    {
        return $this->isStatic;
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
