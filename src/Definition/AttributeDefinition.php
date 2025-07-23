<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition;

/** @internal */
final class AttributeDefinition
{
    public function __construct(
        public readonly ClassDefinition $class,
        /** @var list<mixed> */
        public readonly array $arguments,
    ) {}

    public function instantiate(): object
    {
        return new ($this->class->type->className())(...$this->arguments);
    }
}
