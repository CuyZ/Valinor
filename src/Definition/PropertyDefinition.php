<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition;

use CuyZ\Valinor\Type\FixedType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\EnumType;
use CuyZ\Valinor\Utility\Polyfill;

/** @internal */
final readonly class PropertyDefinition
{
    public function __construct(
        /** @var non-empty-string */
        public string $name,
        /** @var non-empty-string */
        public string $signature,
        public Type $type,
        public Type $nativeType,
        public bool $hasDefaultValue,
        public mixed $defaultValue,
        public bool $isPublic,
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
}
