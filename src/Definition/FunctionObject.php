<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition;

/** @internal */
final class FunctionObject
{
    private FunctionDefinition $definition;

    /** @var callable */
    private $callback;

    public function __construct(FunctionDefinition $definition, callable $callback)
    {
        $this->definition = $definition;
        $this->callback = $callback;
    }

    public function definition(): FunctionDefinition
    {
        return $this->definition;
    }

    public function callback(): callable
    {
        return $this->callback;
    }
}
