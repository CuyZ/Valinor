<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition;

use CuyZ\Valinor\Type\Type;

/** @internal */
final class FunctionDefinition
{
    public function __construct(
        private string $name,
        private string $signature,
        private Attributes $attributes,
        private ?string $fileName,
        /** @var class-string|null */
        private ?string $class,
        private bool $isStatic,
        private bool $isClosure,
        private Parameters $parameters,
        private Type $returnType
    ) {
    }

    public function name(): string
    {
        return $this->name;
    }

    public function signature(): string
    {
        return $this->signature;
    }

    public function attributes(): Attributes
    {
        return $this->attributes;
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

    public function isClosure(): bool
    {
        return $this->isClosure;
    }

    public function parameters(): Parameters
    {
        return $this->parameters;
    }

    public function returnType(): Type
    {
        return $this->returnType;
    }
}
