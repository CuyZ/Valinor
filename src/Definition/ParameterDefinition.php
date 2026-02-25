<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition;

use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\Generics;
use CuyZ\Valinor\Utility\TypeHelper;

use function assert;

/** @internal */
final readonly class ParameterDefinition
{
    public function __construct(
        /** @var non-empty-string */
        public string $name,
        /** @var non-empty-string */
        public string $signature,
        public Type $type,
        public Type $nativeType,
        public bool $isOptional,
        public bool $isVariadic,
        public mixed $defaultValue,
        public Attributes $attributes
    ) {}

    public function forCallable(callable $callable): self
    {
        return new self(
            $this->name,
            $this->signature,
            $this->type,
            $this->nativeType,
            $this->isOptional,
            $this->isVariadic,
            $this->defaultValue,
            $this->attributes->forCallable($callable)
        );
    }

    public function assignGenerics(Generics $generics): self
    {
        assert($generics->items !== []);

        return new self(
            $this->name,
            $this->signature,
            TypeHelper::assignVacantTypes($this->type, $generics->items),
            $this->nativeType,
            $this->isOptional,
            $this->isVariadic,
            $this->defaultValue,
            $this->attributes
        );
    }
}
