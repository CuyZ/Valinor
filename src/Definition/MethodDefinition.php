<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition;

use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\Generics;
use CuyZ\Valinor\Utility\TypeHelper;

/** @internal */
final readonly class MethodDefinition
{
    public function __construct(
        /** @var non-empty-string */
        public string $name,
        /** @var non-empty-string */
        public string $signature,
        public Attributes $attributes,
        public Parameters $parameters,
        public bool $isStatic,
        public bool $isPublic,
        public Type $returnType
    ) {}

    public function assignGenerics(Generics $generics): self
    {
        if ($generics->items === []) {
            return $this;
        }

        return new self(
            $this->name,
            $this->signature,
            $this->attributes,
            $this->parameters->assignGenerics($generics),
            $this->isStatic,
            $this->isPublic,
            TypeHelper::assignVacantTypes($this->returnType, $generics->items),
        );
    }
}
