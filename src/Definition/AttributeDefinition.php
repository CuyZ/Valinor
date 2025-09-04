<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition;

use Closure;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;

/** @internal */
final class AttributeDefinition
{
    /** @var callable */
    private mixed $callable;

    public function __construct(
        public readonly ClassDefinition $class,

        /** @var null|list<array<scalar>|scalar> */
        public readonly ?array $arguments,

        /** @var array{'closure'}
         *     | array{'class', class-string}
         *     | array{'property', class-string, string}
         *     | array{'method', class-string, string}
         *     | array{'methodParameter', class-string, string, int}
         *     | array{'closureParameter', int}
         */
        public readonly array $reflectionParts,
        public readonly int $attributeIndex,
    ) {}

    public function forCallable(callable $callable): self
    {
        $self = clone $this;
        $self->callable = $callable;

        return $self;
    }

    /**
     * There are two ways of instantiating an attribute:
     *
     * 1. If the attribute's arguments contain only scalar, we can directly
     *    instantiate the attribute using its class and arguments.
     * 2. If the attribute arguments contain any object or callable, we are
     *    forced to use reflection to instantiate it, as it is not possible to
     *    properly compile it.
     *
     * The first solution is by far more performant, so we prefer using it when
     * possible.
     */
    public function instantiate(): object
    {
        if ($this->arguments !== null) {
            return new ($this->class->type->className())(...$this->arguments);
        }

        $reflection = match ($this->reflectionParts[0]) {
            'class' => new ReflectionClass($this->reflectionParts[1]),
            'property' => new ReflectionProperty($this->reflectionParts[1], $this->reflectionParts[2]),
            'method' => new ReflectionMethod($this->reflectionParts[1], $this->reflectionParts[2]),
            'methodParameter' => (new ReflectionMethod($this->reflectionParts[1], $this->reflectionParts[2]))->getParameters()[$this->reflectionParts[3]],
            'closure' => new ReflectionFunction(Closure::fromCallable($this->callable)),
            'closureParameter' => new ReflectionParameter(Closure::fromCallable($this->callable), $this->reflectionParts[1]),
        };

        return $reflection->getAttributes()[$this->attributeIndex]->newInstance();
    }
}
