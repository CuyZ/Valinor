<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition;

use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\Generics;
use CuyZ\Valinor\Utility\TypeHelper;

/** @internal */
final readonly class FunctionDefinition
{
    public function __construct(
        /** @var non-empty-string */
        public string $name,
        /** @var non-empty-string */
        public string $signature,
        public Attributes $attributes,
        /** @var non-empty-string|null */
        public ?string $fileName,
        /** @var class-string|null */
        public ?string $class,
        public bool $isStatic,
        public bool $isClosure,
        public Parameters $parameters,
        public Type $returnType,
    ) {}

    public function forCallable(callable $callable): self
    {
        return new self(
            $this->name,
            $this->signature,
            $this->attributes->forCallable($callable),
            $this->fileName,
            $this->class,
            $this->isStatic,
            $this->isClosure,
            $this->parameters->forCallable($callable),
            $this->returnType
        );
    }

    public function assignGenerics(Generics $generics): self
    {
        if ($generics->items === []) {
            return $this;
        }

        return new self(
            $this->name,
            $this->signature,
            $this->attributes,
            $this->fileName,
            $this->class,
            $this->isStatic,
            $this->isClosure,
            $this->parameters->assignGenerics($generics),
            TypeHelper::assignVacantTypes($this->returnType, $generics->items),
        );
    }
}
