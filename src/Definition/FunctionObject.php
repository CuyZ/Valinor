<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition;

/** @internal */
final class FunctionObject
{
    public readonly FunctionDefinition $definition;

    /** @var callable */
    public readonly mixed $callback;

    public function __construct(FunctionDefinition $definition, callable $callback)
    {
        $this->definition = $definition;
        $this->callback = $callback;
    }
}
