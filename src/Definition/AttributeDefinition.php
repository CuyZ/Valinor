<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition;

/** @internal */
final class AttributeDefinition
{
    public function __construct(
        private ClassDefinition $class,
        /** @var list<mixed> */
        private array $arguments,
    ) {}

    public function class(): ClassDefinition
    {
        return $this->class;
    }

    /**
     * @return list<mixed>
     */
    public function arguments(): array
    {
        return $this->arguments;
    }

    public function instantiate(): object
    {
        return new ($this->class->type()->className())(...$this->arguments);
    }
}
