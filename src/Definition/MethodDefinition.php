<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition;

use CuyZ\Valinor\Type\Type;

/** @internal */
final class MethodDefinition
{
    public function __construct(
        private string $name,
        private string $signature,
        private Parameters $parameters,
        private bool $isStatic,
        private bool $isPublic,
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

    public function parameters(): Parameters
    {
        return $this->parameters;
    }

    public function isStatic(): bool
    {
        return $this->isStatic;
    }

    public function isPublic(): bool
    {
        return $this->isPublic;
    }

    public function returnType(): Type
    {
        return $this->returnType;
    }
}
