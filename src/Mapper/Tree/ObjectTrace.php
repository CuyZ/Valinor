<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree;

use CuyZ\Valinor\Type\ObjectType;
use CuyZ\Valinor\Type\Type;

/** @internal */
final class ObjectTrace
{
    /** @var array<class-string, int> */
    private array $trace = [];

    public function markAsVisited(Type $type): self
    {
        if (! $type instanceof ObjectType) {
            return $this;
        }

        $self = clone $this;
        $self->trace[$type->className()] ??= 0;
        $self->trace[$type->className()] += 1;

        return $self;
    }

    public function hasDetectedCircularDependency(Type $type): bool
    {
        return $type instanceof ObjectType
            && ($this->trace[$type->className()] ?? null) === 2;
    }
}
