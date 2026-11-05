<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition;

use CuyZ\Valinor\Type\FixedType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\EnumType;
use CuyZ\Valinor\Type\Types\Generics;
use CuyZ\Valinor\Type\Types\GenericType;
use CuyZ\Valinor\Utility\Polyfill;
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
        public Attributes $attributes,
        public bool $isFixed,
    ) {}

    public function fixedValue(): mixed
    {
        if ($this->type instanceof FixedType) {
            return $this->type->value();
        }

        if ($this->type instanceof EnumType) {
            return Polyfill::array_first($this->type->cases());
        }

        return null;
    }

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
            $this->attributes->forCallable($callable),
            $this->isFixed,
        );
    }

    public function assignGenerics(Generics $generics): self
    {
        assert($generics->items !== []);

        $type = TypeHelper::assignVacantTypes($this->type, $generics->items);

        return new self(
            $this->name,
            $this->signature,
            $type,
            $this->nativeType,
            $this->isOptional,
            $this->isVariadic,
            $this->defaultValue,
            $this->attributes,
            $this->type instanceof GenericType ? TypeHelper::acceptsSingleValue($type) : $this->isFixed,
        );
    }
}
